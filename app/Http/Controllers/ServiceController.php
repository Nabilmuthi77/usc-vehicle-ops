<?php

namespace App\Http\Controllers;

use App\Enums\CostBorneBy;
use App\Enums\ServiceCategory;
use App\Enums\ServiceScheduleStatus;
use App\Http\Requests\StoreServiceRecordRequest;
use App\Models\ServiceRecord;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehicleServiceSchedule;
use App\Models\Vendor;
use App\Services\FileUploadService;
use App\Services\OdometerService;
use App\Services\ServiceScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Modul M4 — servis berkala berbasis kilometer (PRD §7.4). */
class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceScheduleService $schedules,
        private readonly OdometerService $odometer,
        private readonly FileUploadService $uploads,
        private readonly \App\Services\NotificationDispatcher $notifier,
    ) {}

    /**
     * FR-M4-06 — dashboard servis: unit segera & jatuh tempo diurutkan
     * dari yang paling mendesak.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceRecord::class);

        $schedules = VehicleServiceSchedule::query()
            ->with(['vehicle.rentalContract.vendor', 'serviceType'])
            ->active()
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($query) use ($search) {
                    $query->whereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', "%{$search}%"))
                          ->orWhereHas('serviceType', fn ($st) => $st->where('name', 'like', "%{$search}%"))
                          ->orWhere('interval_km', 'like', "%{$search}%")
                          ->orWhere('interval_months', 'like', "%{$search}%")
                          ->orWhere('remaining_km', 'like', "%{$search}%")
                          ->orWhere('estimated_due_date', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('ownership'), fn ($q) => $q->whereHas(
                'vehicle',
                fn ($v) => $v->where('ownership', $request->string('ownership')),
            ))
            ->orderByRaw("CASE status WHEN 'jatuh_tempo' THEN 0 WHEN 'segera' THEN 1 ELSE 2 END")
            ->orderBy('remaining_km')
            ->paginate(10)
            ->withQueryString();

        return view('service.index', [
            'schedules' => $schedules,
            'summary' => [
                'jatuh_tempo' => VehicleServiceSchedule::active()->ofStatus(ServiceScheduleStatus::JatuhTempo)->count(),
                'segera' => VehicleServiceSchedule::active()->ofStatus(ServiceScheduleStatus::Segera)->count(),
                'aman' => VehicleServiceSchedule::active()->ofStatus(ServiceScheduleStatus::Aman)->count(),
            ],
            'statuses' => ServiceScheduleStatus::options(),
            'filters' => $request->only(['q', 'status', 'ownership']),
        ]);
    }

    /** FR-M4-10 — kartu riwayat servis per kendaraan beserta akumulasi biaya. */
    public function history(Vehicle $vehicle): View
    {
        $this->authorize('viewAny', ServiceRecord::class);

        $records = $vehicle->serviceRecords()
            ->with(['serviceType', 'vendor', 'items'])
            ->latest()
            ->paginate(25);

        return view('service.history', [
            'vehicle' => $vehicle,
            'records' => $records,
            'schedules' => $vehicle->serviceSchedules()->with('serviceType')->get(),
            // FR-M4-12 — biaya perusahaan dipisahkan dari biaya vendor.
            'totalCompanyCost' => (float) $vehicle->serviceRecords()->borneByCompany()->sum('total_cost'),
            'totalVendorCost' => (float) $vehicle->serviceRecords()
                ->where('cost_borne_by', CostBorneBy::Vendor)->sum('total_cost'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ServiceRecord::class);

        return view('service.create', $this->formOptions() + [
            'selectedVehicle' => $request->filled('vehicle_id')
                ? Vehicle::find($request->integer('vehicle_id'))
                : null,
        ]);
    }

    /**
     * FR-M4-08 & FR-M4-09 — simpan realisasi servis; jadwal berikutnya
     * di-generate otomatis.
     */
    public function store(StoreServiceRecordRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::findOrFail($request->integer('vehicle_id'));

        $record = DB::transaction(function () use ($request, $vehicle) {
            $data = $request->safe()->except(['attachment', 'items']);

            // BR-16 — kepemilikan menentukan penanggung biaya secara default.
            $data['cost_borne_by'] = $request->input('cost_borne_by')
                ?? $vehicle->defaultCostBorneBy()->value;

            if ($request->hasFile('attachment')) {
                $data['attachment_path'] = $this->uploads->storeImage(
                    $request->file('attachment'),
                    'services/'.$vehicle->getKey(),
                );
            }

            $data['created_by'] = $request->user()->getKey();

            $record = ServiceRecord::create($data);

            foreach ($request->input('items', []) as $item) {
                $record->items()->create($item);
            }

            // Total biaya mengikuti rincian item bila tidak diisi manual.
            if ($request->input('total_cost') === null && $record->items()->exists()) {
                $record->forceFill(['total_cost' => $record->calculateItemsTotal()])->save();
            }

            $vehicle->forceFill(['status' => \App\Enums\VehicleStatus::Tersedia])->save();

            return $record;
        });

        // FR-M4-03 — odometer kendaraan ikut terbarui dari input servis.
        if ((int) $record->odometer > (int) $vehicle->current_odometer) {
            $this->odometer->record(
                vehicle: $vehicle,
                odometer: (int) $record->odometer,
                source: \App\Enums\OdometerSource::Service,
                recordedAt: $record->service_date->copy()->startOfDay(),
                sourceable: $record,
                recordedBy: $request->user(),
                notes: 'Realisasi servis '.($record->serviceType?->name ?? 'insidental'),
            );
        }

        // FR-M4-09 — generate jadwal servis berikutnya.
        $schedule = $this->schedules->applyServiceRecord($record);

        if ($record->cost_borne_by->countsAsCompanyCost()) {
            $financeUsers = \App\Models\User::query()
                ->role(\App\Models\User::ROLE_VIEWER)
                ->whereHas('department', fn ($q) => $q->where('name', 'Finance'))
                ->get();

            $this->notifier->sendMany(
                $financeUsers,
                new \App\Notifications\CompanyServiceRecordCreated($record)
            );
        }

        $message = 'Realisasi servis tersimpan.';

        if ($schedule !== null) {
            $message .= ' Jadwal berikutnya pada odometer '
                .number_format((int) $schedule->next_due_odometer, 0, ',', '.').' km.';
        }

        return redirect()->route('service.history', $vehicle)->with('status', $message);
    }

    public function show(ServiceRecord $serviceRecord): View
    {
        $this->authorize('view', $serviceRecord);

        $serviceRecord->load(['vehicle', 'serviceType', 'vendor', 'items', 'serviceRequest']);

        return view('service.show', ['record' => $serviceRecord]);
    }

    /** FR-M4-01 — pengaturan interval servis per unit kendaraan. */
    public function editSchedule(VehicleServiceSchedule $schedule): View
    {
        $this->authorize('create', ServiceRecord::class);

        return view('service.edit-schedule', [
            'schedule' => $schedule->load(['vehicle', 'serviceType']),
        ]);
    }

    public function updateSchedule(Request $request, VehicleServiceSchedule $schedule): RedirectResponse
    {
        $this->authorize('create', ServiceRecord::class);

        $validated = $request->validate([
            'interval_km' => ['nullable', 'integer', 'min:0'],
            'interval_months' => ['nullable', 'integer', 'min:0', 'max:120'],
            'last_service_odometer' => ['nullable', 'integer', 'min:0'],
            'last_service_date' => ['nullable', 'date', 'before_or_equal:today'],
            'is_active' => ['required', 'boolean'],
        ]);

        $schedule->fill($validated)->save();

        $this->schedules->recalculate($schedule->refresh());

        return redirect()
            ->route('service.index')
            ->with('status', 'Jadwal servis diperbarui dan status dihitung ulang.');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'vehicles' => Vehicle::orderBy('plate_number')->get(),
            'serviceTypes' => ServiceType::active()->latest()->get(),
            'vendors' => Vendor::active()->latest()->get(),
            'categories' => ServiceCategory::options(),
            'costBorneByOptions' => CostBorneBy::options(),
        ];
    }
}
