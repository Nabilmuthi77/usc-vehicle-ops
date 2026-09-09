<?php

namespace App\Services;

use App\Enums\OdometerSource;
use App\Enums\ServiceCategory;
use App\Enums\ServiceRequestStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\ServiceRecord;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * FR-M4-15 s.d. FR-M4-20 — permintaan servis ke vendor.
 *
 * Alur: dibuat → dikirim → dijadwalkan → dikerjakan → selesai / dibatalkan.
 */
class ServiceRequestService
{
    public function __construct(
        private readonly DocumentNumberService $numbers,
        private readonly OdometerService $odometer,
        private readonly ServiceScheduleService $schedules,
    ) {}

    /**
     * FR-M4-15 & FR-M4-16 — buat permintaan servis bernomor otomatis.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, Vehicle $vehicle, User $actor): ServiceRequest
    {
        return DB::transaction(function () use ($data, $vehicle, $actor) {
            $vehicle->forceFill(['status' => \App\Enums\VehicleStatus::Servis])->save();

            return ServiceRequest::create([
                ...$data,
                'request_number' => $this->numbers->generate(
                    'document_prefix_service_request',
                    'service_requests',
                    'request_number',
                ),
                'vehicle_id' => $vehicle->getKey(),
                'current_odometer' => $data['current_odometer'] ?? $vehicle->current_odometer,
                'status' => ServiceRequestStatus::Dibuat,
                'created_by' => $actor->getKey(),
            ]);
        });
    }

    /**
     * FR-M4-17 — kirim permintaan ke PIC vendor via email dan catat tanggalnya.
     *
     * Kegagalan SMTP tidak membatalkan pencatatan status: PRD §7.7 menetapkan
     * in-app sebagai fallback bila SMTP tidak tersedia.
     */
    public function sendToVendor(ServiceRequest $request, string $email, User $actor): ServiceRequest
    {
        $this->assertTransition($request, ServiceRequestStatus::Dikirim);

        $request->fill([
            'status' => ServiceRequestStatus::Dikirim,
            'sent_at' => Carbon::now(),
            'sent_to_email' => $email,
        ])->save();

        try {
            Mail::to($email)->send(new \App\Mail\ServiceRequestMail($request));
        } catch (\Throwable $exception) {
            report($exception);

            activity('ServiceRequest')
                ->performedOn($request)
                ->causedBy($actor)
                ->log('Pengiriman email permintaan servis gagal: '.$exception->getMessage());
        }

        return $request;
    }

    /** Ubah status mengikuti transisi yang diizinkan (FR-M4-16). */
    public function transitionTo(
        ServiceRequest $request,
        ServiceRequestStatus $status,
        array $data = [],
    ): ServiceRequest {
        $this->assertTransition($request, $status);

        $request->status = $status;

        $statusDate = $data['status_date'] ?? null;

        if ($status === ServiceRequestStatus::Dikirim && $statusDate) {
            $request->sent_at = Carbon::parse($statusDate)->startOfDay();
        }

        if ($status === ServiceRequestStatus::Dijadwalkan) {
            $request->scheduled_date = $statusDate;
        }

        if (isset($data['vendor_response_note'])) {
            $request->vendor_response_note = $data['vendor_response_note'];
        }

        if ($status === ServiceRequestStatus::Dibatalkan) {
            $request->vehicle->forceFill(['status' => \App\Enums\VehicleStatus::Tersedia])->save();
        }

        $request->save();

        return $request;
    }

    /**
     * FR-M4-19 — penyelesaian permintaan servis.
     *
     * Odometer, tanggal, dan ringkasan pekerjaan dicatat; biaya bersifat
     * opsional dan untuk kendaraan sewa tidak dihitung sebagai biaya
     * perusahaan (BR-16 menetapkan `cost_borne_by = vendor`).
     *
     * @param  array<string, mixed>  $data
     */
    public function complete(ServiceRequest $request, array $data, User $actor): ServiceRecord
    {
        $this->assertTransition($request, ServiceRequestStatus::Selesai);

        return DB::transaction(function () use ($request, $data, $actor) {
            $vehicle = $request->vehicle;
            $completedDate = Carbon::parse($data['completed_date'] ?? Carbon::today());
            $odometer = (int) ($data['odometer'] ?? $vehicle->current_odometer);

            $record = ServiceRecord::create([
                'vehicle_id' => $vehicle->getKey(),
                'service_type_id' => $request->service_type_id,
                'service_request_id' => $request->getKey(),
                'category' => ServiceCategory::Berkala,
                'service_date' => $completedDate->toDateString(),
                'odometer' => $odometer,
                'vendor_id' => $request->vendor_id,
                'invoice_number' => $data['invoice_number'] ?? null,
                'total_cost' => $data['total_cost'] ?? null,
                // BR-16 — kepemilikan menentukan penanggung biaya.
                'cost_borne_by' => $vehicle->defaultCostBorneBy(),
                'description' => $data['description'] ?? $request->complaint_note,
                'created_by' => $actor->getKey(),
            ]);

            $request->fill([
                'status' => ServiceRequestStatus::Selesai,
                'completed_date' => $completedDate->toDateString(),
                'vendor_response_note' => $data['vendor_response_note'] ?? $request->vendor_response_note,
                // FR-M4-18 — SLA vendor & downtime kendaraan.
                'response_days' => $request->sent_at
                    ? max(0, (int) $request->sent_at->startOfDay()->diffInDays($completedDate, false))
                    : null,
                'downtime_days' => $data['downtime_days'] ?? ($request->scheduled_date
                    ? max(0, (int) Carbon::parse($request->scheduled_date)->diffInDays($completedDate, false))
                    : null),
            ])->save();

            if ($odometer > (int) $vehicle->current_odometer) {
                $this->odometer->record(
                    vehicle: $vehicle,
                    odometer: $odometer,
                    source: OdometerSource::Service,
                    recordedAt: $completedDate,
                    sourceable: $record,
                    recordedBy: $actor,
                    notes: 'Servis vendor '.$request->request_number,
                );
            }

            // FR-M4-09 — jadwal berikutnya di-generate otomatis.
            $this->schedules->applyServiceRecord($record);

            $vehicle->forceFill(['status' => \App\Enums\VehicleStatus::Tersedia])->save();

            return $record;
        });
    }

    /**
     * FR-M4-21 — rekap performa vendor sewa untuk evaluasi kontrak.
     *
     * @return array<int, array<string, mixed>>
     */
    public function vendorPerformance(): array
    {
        return ServiceRequest::query()
            ->with('vendor')
            ->whereNotNull('sent_at')
            ->get()
            ->groupBy('vendor_id')
            ->map(function ($requests) {
                $completed = $requests->whereNotNull('response_days');

                return [
                    'vendor' => $requests->first()->vendor?->name,
                    'total_requests' => $requests->count(),
                    'open_requests' => $requests->filter(
                        fn (ServiceRequest $r) => ! $r->status->isClosed()
                    )->count(),
                    'avg_response_days' => $completed->isEmpty()
                        ? null
                        : round((float) $completed->avg('response_days'), 1),
                    'total_downtime_days' => (int) $requests->sum('downtime_days'),
                ];
            })
            ->sortByDesc('total_requests')
            ->values()
            ->all();
    }

    /** @throws BusinessRuleException */
    private function assertTransition(ServiceRequest $request, ServiceRequestStatus $target): void
    {
        if (! $request->canTransitionTo($target)) {
            throw BusinessRuleException::rule('FR-M4-16', sprintf(
                'Status permintaan servis tidak dapat berubah dari "%s" menjadi "%s".',
                $request->status->label(),
                $target->label(),
            ));
        }
    }
}
