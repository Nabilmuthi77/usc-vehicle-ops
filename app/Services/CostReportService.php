<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\CostBorneBy;
use App\Models\Booking;
use App\Models\FuelTransaction;
use App\Models\ServiceRecord;
use App\Models\TollTransaction;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modul M6 — perhitungan biaya operasional (PRD §7.6).
 *
 * BR-09 — biaya periode = Σ klaim BBM terverifikasi + Σ biaya tol
 *          + Σ biaya servis yang ditanggung perusahaan.
 *          Biaya servis kendaraan sewa yang ditanggung vendor dikecualikan.
 * BR-10 — cost per km = total biaya periode ÷ total km pada periode sama.
 */
class CostReportService
{
    /**
     * FR-M6-03 — laporan biaya operasional gabungan per kendaraan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function costByVehicle(
        ?string $from = null,
        ?string $to = null,
        ?int $departmentId = null,
    ): array {
        $vehicles = Vehicle::query()
            ->with('department')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->orderBy('plate_number')
            ->get();

        $fuel = $this->fuelCostPerVehicle($from, $to);
        $toll = $this->tollCostPerVehicle($from, $to);
        $service = $this->serviceCostPerVehicle($from, $to);
        $distance = $this->distancePerVehicle($from, $to);

        return $vehicles->map(function (Vehicle $vehicle) use ($fuel, $toll, $service, $distance, $from, $to) {
            $id = $vehicle->getKey();

            $fuelCost = (float) ($fuel[$id] ?? 0);
            $tollCost = (float) ($toll[$id] ?? 0);
            $serviceCost = (float) ($service[$id] ?? 0);
            $km = (int) ($distance[$id] ?? 0);
            $total = $fuelCost + $tollCost + $serviceCost;

            return [
                'vehicle_id' => $id,
                'plate_number' => $vehicle->plate_number,
                'vehicle' => $vehicle->full_name,
                'ownership' => $vehicle->ownership->value,
                'department' => $vehicle->department?->name,
                'fuel_cost' => $fuelCost,
                'toll_cost' => $tollCost,
                'service_cost' => $serviceCost,
                'total_cost' => $total,
                'distance_km' => $km,
                // BR-10 — cost per km.
                'cost_per_km' => $km > 0 ? round($total / $km, 2) : 0.0,
                // FR-M4-12 — biaya vendor dipisahkan sebagai informasi.
                'vendor_borne_cost' => (float) $this->vendorBorneServiceCost($id, $from, $to),
            ];
        })->all();
    }

    /**
     * BR-09 & BR-14 — biaya BBM hanya dari klaim terverifikasi/dibayar,
     * memakai nominal terkoreksi bila ada.
     *
     * @return array<int, float>
     */
    public function fuelCostPerVehicle(?string $from, ?string $to): array
    {
        return FuelTransaction::query()
            ->countedAsCost()
            ->betweenDates($from, $to)
            ->groupBy('vehicle_id')
            ->select('vehicle_id', DB::raw('SUM(COALESCE(approved_amount, total_cost)) as total'))
            ->pluck('total', 'vehicle_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /** @return array<int, float> */
    public function tollCostPerVehicle(?string $from, ?string $to): array
    {
        return TollTransaction::query()
            ->betweenDates($from, $to)
            ->groupBy('vehicle_id')
            ->select('vehicle_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'vehicle_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * BR-09 — hanya biaya servis yang ditanggung perusahaan.
     *
     * @return array<int, float>
     */
    public function serviceCostPerVehicle(?string $from, ?string $to): array
    {
        return ServiceRecord::query()
            ->borneByCompany()
            ->betweenDates($from, $to)
            ->groupBy('vehicle_id')
            ->select('vehicle_id', DB::raw('SUM(COALESCE(total_cost, 0)) as total'))
            ->pluck('total', 'vehicle_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /** FR-M4-12 — biaya servis yang ditanggung vendor (di luar biaya perusahaan). */
    public function vendorBorneServiceCost(int $vehicleId, ?string $from, ?string $to): float
    {
        return (float) ServiceRecord::query()
            ->where('vehicle_id', $vehicleId)
            ->where('cost_borne_by', CostBorneBy::Vendor)
            ->betweenDates($from, $to)
            ->sum('total_cost');
    }

    /**
     * BR-10 — total km ditempuh pada periode, dihitung dari peminjaman selesai.
     *
     * @return array<int, int>
     */
    public function distancePerVehicle(?string $from, ?string $to): array
    {
        return Booking::query()
            ->where('status', BookingStatus::Selesai)
            ->whereNotNull('distance_traveled')
            ->betweenDates($from, $to)
            ->groupBy('vehicle_id')
            ->select('vehicle_id', DB::raw('SUM(distance_traveled) as total'))
            ->pluck('total', 'vehicle_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * FR-M6-02 — tren biaya operasional 12 bulan terakhir per kategori.
     *
     * @return array{months: array<int, string>, bbm: array<int, float>, tol: array<int, float>, servis: array<int, float>}
     */
    public function monthlyCostTrend(int $months = 12): array
    {
        $start = Carbon::today()->subMonths($months - 1)->startOfMonth();

        $periods = collect(range(0, $months - 1))
            ->map(fn (int $offset) => $start->copy()->addMonths($offset)->format('Y-m'));

        $fuel = $this->sumByMonth(
            FuelTransaction::query()->countedAsCost(),
            'transaction_datetime',
            'COALESCE(approved_amount, total_cost)',
            $start,
        );

        $toll = $this->sumByMonth(
            TollTransaction::query(),
            'transaction_datetime',
            'amount',
            $start,
        );

        $service = $this->sumByMonth(
            ServiceRecord::query()->borneByCompany(),
            'service_date',
            'COALESCE(total_cost, 0)',
            $start,
        );

        return [
            'months' => $periods->all(),
            'bbm' => $periods->map(fn ($p) => (float) ($fuel[$p] ?? 0))->all(),
            'tol' => $periods->map(fn ($p) => (float) ($toll[$p] ?? 0))->all(),
            'servis' => $periods->map(fn ($p) => (float) ($service[$p] ?? 0))->all(),
        ];
    }

    /**
     * Agregasi bulanan yang portabel antara MySQL dan SQLite.
     *
     * @return array<string, float>
     */
    private function sumByMonth($query, string $dateColumn, string $amountExpression, Carbon $since): array
    {
        $driver = $query->getModel()->getConnection()->getDriverName();

        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', {$dateColumn})"
            : "DATE_FORMAT({$dateColumn}, '%Y-%m')";

        return $query
            ->where($dateColumn, '>=', $since)
            ->groupBy(DB::raw($monthExpression))
            ->select(
                DB::raw("{$monthExpression} as period"),
                DB::raw("SUM({$amountExpression}) as total"),
            )
            ->pluck('total', 'period')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * FR-M6-04 — laporan utilisasi kendaraan (% hari terpakai pada periode).
     *
     * @return array<int, array<string, mixed>>
     */
    public function utilization(?string $from = null, ?string $to = null): array
    {
        $start = Carbon::parse($from ?? Carbon::today()->startOfMonth());
        $end = Carbon::parse($to ?? Carbon::today()->endOfMonth());
        $totalDays = max(1, (int) $start->diffInDays($end) + 1);

        $bookings = Booking::query()
            ->with('vehicle')
            ->whereNotNull('vehicle_id')
            ->whereIn('status', [BookingStatus::Selesai, BookingStatus::SedangDigunakan])
            ->betweenDates($start->toDateString(), $end->toDateString())
            ->get(['vehicle_id', 'booking_date', 'distance_traveled']);

        return $bookings
            ->groupBy('vehicle_id')
            ->map(function (Collection $group, $vehicleId) use ($totalDays) {
                $daysUsed = $group->pluck('booking_date')
                    ->map(fn ($date) => Carbon::parse($date)->toDateString())
                    ->unique()
                    ->count();

                return [
                    'vehicle_id' => $vehicleId,
                    'vehicle' => $group->first()->vehicle?->plate_number,
                    'days_used' => $daysUsed,
                    'total_days' => $totalDays,
                    'utilization_percent' => round($daysUsed / $totalDays * 100, 1),
                    'trips' => $group->count(),
                    'distance_km' => (int) $group->sum('distance_traveled'),
                ];
            })
            ->sortByDesc('utilization_percent')
            ->values()
            ->all();
    }

    /**
     * FR-M6-04 — laporan aktivitas driver.
     *
     * @return array<int, array<string, mixed>>
     */
    public function driverActivity(?string $from = null, ?string $to = null, ?int $departmentId = null): array
    {
        $bookings = Booking::query()
            ->with('driver')
            ->whereNotNull('driver_id')
            ->when($departmentId, fn ($q) => $q->whereHas('driver.user', fn ($q) => $q->where('department_id', $departmentId)))
            ->where('status', BookingStatus::Selesai)
            ->betweenDates($from, $to)
            ->get(['driver_id', 'distance_traveled']);

        $claims = FuelTransaction::query()
            ->whereNotNull('driver_id')
            ->when($departmentId, fn ($q) => $q->whereHas('driver.user', fn ($q) => $q->where('department_id', $departmentId)))
            ->countedAsCost()
            ->betweenDates($from, $to)
            ->get(['driver_id', 'consumption_km_per_liter']);

        return $bookings
            ->groupBy('driver_id')
            ->map(function (Collection $group, $driverId) use ($claims) {
                $driverClaims = $claims->where('driver_id', $driverId);
                $consumption = $driverClaims->whereNotNull('consumption_km_per_liter');

                return [
                    'driver_id' => $driverId,
                    'driver' => $group->first()->driver?->name,
                    'trips' => $group->count(),
                    'distance_km' => (int) $group->sum('distance_traveled'),
                    'fuel_claims' => $driverClaims->count(),
                    'avg_consumption' => $consumption->isEmpty()
                        ? null
                        : round((float) $consumption->avg('consumption_km_per_liter'), 2),
                ];
            })
            ->sortByDesc('trips')
            ->values()
            ->all();
    }

    /** FR-M6-02 — lima kendaraan dengan biaya operasional tertinggi. */
    public function topCostlyVehicles(int $limit = 5, ?string $from = null, ?string $to = null): array
    {
        return collect($this->costByVehicle($from, $to))
            ->sortByDesc('total_cost')
            ->take($limit)
            ->values()
            ->all();
    }


    /** FR-M3-17 — rekap biaya BBM per driver pada periode. */
    public function fuelCostByDriver(?string $from = null, ?string $to = null): array
    {
        return FuelTransaction::query()
            ->with('driver')
            ->countedAsCost()
            ->whereNotNull('driver_id')
            ->betweenDates($from, $to)
            ->get()
            ->groupBy('driver_id')
            ->map(fn (Collection $rows) => [
                'driver' => $rows->first()->driver?->name,
                'transaction_count' => $rows->count(),
                'liters' => round((float) $rows->sum('liters'), 2),
                'total_cost' => $rows->sum(fn (FuelTransaction $t) => $t->claimable_amount),
            ])
            ->sortByDesc('total_cost')
            ->values()
            ->all();
    }

    /** FR-M6-01 — total biaya operasional bulan berjalan. */
    public function currentMonthTotal(): float
    {
        $from = Carbon::today()->startOfMonth()->toDateString();
        $to = Carbon::today()->endOfMonth()->toDateString();

        return (float) collect($this->costByVehicle($from, $to))->sum('total_cost');
    }
}
