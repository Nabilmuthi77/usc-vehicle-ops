<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\ServiceScheduleStatus;
use App\Enums\VehicleStatus;
use App\Models\Booking;
use App\Models\FuelTransaction;
use App\Models\Vehicle;
use App\Models\VehicleServiceSchedule;
use App\Services\CostReportService;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** FR-M6-01 & FR-M6-02 — dashboard utama. */
class DashboardController extends Controller
{
    public function __construct(private readonly CostReportService $costs) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // FR-M6-06 — Karyawan & Driver hanya melihat aktivitas dirinya.
        if ($user->seesOnlyOwnRecords()) {
            return view('dashboard', $this->personalDashboard($request));
        }

        $vehicleCounts = Vehicle::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard', [
            'personal' => false,
            'stats' => [
                'total_kendaraan' => Vehicle::count(),
                'tersedia' => (int) ($vehicleCounts[VehicleStatus::Tersedia->value] ?? 0),
                'dipinjam' => (int) ($vehicleCounts[VehicleStatus::Dipinjam->value] ?? 0),
                'servis' => (int) ($vehicleCounts[VehicleStatus::Servis->value] ?? 0),
                'menunggu_approval' => Booking::pendingApproval()->count(),
                'servis_jatuh_tempo' => VehicleServiceSchedule::active()
                    ->ofStatus(ServiceScheduleStatus::JatuhTempo)->count(),
                'biaya_bulan_ini' => $this->costs->currentMonthTotal(),
            ],
            'trend' => $this->costs->monthlyCostTrend(),
            'topCostly' => $this->costs->topCostlyVehicles(5),
            'utilization' => array_slice($this->costs->utilization(), 0, 5),
            // FR-M4-06 — servis paling mendesak.
            'urgentServices' => VehicleServiceSchedule::query()
                ->with(['vehicle', 'serviceType'])
                ->needsAttention()
                ->limit(5)
                ->get(),
            'pendingBookings' => Booking::query()
                ->with(['requester', 'department'])
                ->pendingApproval()
                ->orderBy('booking_date')
                ->limit(5)
                ->get(),
            'pendingClaims' => $user->can(Permissions::FUEL_CLAIM_VERIFY)
                ? FuelTransaction::awaitingVerification()->count()
                : 0,
        ]);
    }

    /**
     * Dashboard ringkas bagi Karyawan & Driver (FR-M6-06).
     *
     * @return array<string, mixed>
     */
    private function personalDashboard(Request $request): array
    {
        $user = $request->user();

        return [
            'personal' => true,
            'stats' => [
                'peminjaman_aktif' => Booking::ownedBy($user)
                    ->whereIn('status', [BookingStatus::Disetujui, BookingStatus::SedangDigunakan])
                    ->count(),
                'menunggu_approval' => Booking::ownedBy($user)->pendingApproval()->count(),
                'klaim_diajukan' => FuelTransaction::where('claimant_id', $user->getKey())
                    ->awaitingVerification()->count(),
                'klaim_outstanding' => (float) FuelTransaction::where('claimant_id', $user->getKey())
                    ->outstanding()
                    ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(approved_amount, total_cost)')),
            ],
            'myBookings' => Booking::query()
                ->with(['vehicle', 'driver'])
                ->ownedBy($user)
                ->orderByDesc('booking_date')
                ->limit(5)
                ->get(),
            'myClaims' => FuelTransaction::query()
                ->with('vehicle')
                ->where('claimant_id', $user->getKey())
                ->orderByDesc('transaction_datetime')
                ->limit(5)
                ->get(),
        ];
    }
}
