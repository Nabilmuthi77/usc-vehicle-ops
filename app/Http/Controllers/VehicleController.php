<?php

namespace App\Http\Controllers;

use App\Enums\FuelType;
use App\Enums\OdometerSource;
use App\Enums\Ownership;
use App\Enums\VehicleStatus;
use App\Exceptions\BusinessRuleException;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Department;
use App\Models\RentalContract;
use App\Models\Vehicle;
use App\Services\FileUploadService;
use App\Services\OdometerService;
use App\Services\ServiceScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Modul M1 — master data kendaraan (PRD §7.1). */
class VehicleController extends Controller
{
    public function __construct(
        private readonly FileUploadService $uploads,
        private readonly OdometerService $odometer,
        private readonly ServiceScheduleService $schedules,
    ) {}

    /** FR-M1-02 & FR-M1-11 — daftar kendaraan beserta penanda ganjil/genap. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = Vehicle::query()
            ->with(['department', 'serviceSchedules'])
            ->search($request->input('search'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('ownership'), fn ($q) => $q->where('ownership', $request->string('ownership')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('vehicles.index', [
            'vehicles' => $vehicles,
            'statuses' => VehicleStatus::options(),
            'ownerships' => Ownership::options(),
            'departments' => Department::active()->latest()->get(['id', 'name']),
            'filters' => $request->only(['search', 'status', 'ownership', 'department_id']),
        ]);
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = Vehicle::query()
            ->with(['department'])
            ->search($request->input('search'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('ownership'), fn ($q) => $q->where('ownership', $request->string('ownership')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->latest()
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VehicleExport($vehicles),
            'master-data-kendaraan.xlsx'
        );
    }

    public function create(): View
    {
        $this->authorize('create', Vehicle::class);

        return view('vehicles.create', $this->formOptions());
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->uploads->storeImage($request->file('photo'), 'vehicles');
        }

        $vehicle = Vehicle::create($data);

        // FR-M4-01 — siapkan jadwal servis untuk seluruh jenis servis aktif.
        $this->schedules->bootstrapForVehicle($vehicle);

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('status', "Kendaraan {$vehicle->plate_number} berhasil ditambahkan.");
    }

    public function show(Vehicle $vehicle): View
    {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'department', 'rentalContract.vendor', 'tollCard',
            'documents', 'serviceSchedules.serviceType',
        ]);

        return view('vehicles.show', [
            'vehicle' => $vehicle,
            'recentBookings' => $vehicle->bookings()
                ->with(['requester', 'driver'])
                ->orderByDesc('booking_date')
                ->limit(10)
                ->get(),
            'recentOdometerLogs' => $vehicle->odometerLogs()
                ->with('recordedBy')
                ->orderByDesc('recorded_at')
                ->limit(10)
                ->get(),
            'averageDailyUsage' => $this->odometer->averageDailyUsage($vehicle),
        ]);
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.edit', $this->formOptions() + ['vehicle' => $vehicle]);
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'current_odometer']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->uploads->replaceImage(
                $vehicle->photo_path,
                $request->file('photo'),
                'vehicles',
            );
        }

        $vehicle->update($data);

        // BR-03 — perubahan odometer selalu melewati OdometerService.
        $newOdometer = $request->integer('current_odometer');

        if ($newOdometer !== (int) $vehicle->current_odometer) {
            try {
                $this->odometer->record(
                    vehicle: $vehicle,
                    odometer: $newOdometer,
                    source: OdometerSource::Manual,
                    recordedBy: $request->user(),
                    notes: 'Penyuntingan master kendaraan',
                );
            } catch (BusinessRuleException $exception) {
                return back()->withInput()->withErrors(['current_odometer' => $exception->getMessage()]);
            }
        }

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('status', 'Data kendaraan diperbarui.');
    }

    /** BR-11 — soft delete, tercatat di audit log. */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);

        $vehicle->delete();

        return redirect()
            ->route('vehicles.index')
            ->with('status', "Kendaraan {$vehicle->plate_number} dinonaktifkan dari daftar.");
    }

    /**
     * BR-03 — koreksi odometer mundur oleh Admin, wajib beralasan.
     */
    public function correctOdometer(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('correctOdometer', $vehicle);

        $validated = $request->validate(
            [
                'odometer' => ['required', 'integer', 'min:0'],
                'correction_reason' => ['required', 'string', 'max:2000'],
            ],
            ['correction_reason.required' => 'Alasan koreksi odometer wajib diisi.'],
        );

        try {
            $this->odometer->record(
                vehicle: $vehicle,
                odometer: (int) $validated['odometer'],
                source: OdometerSource::Manual,
                recordedBy: $request->user(),
                notes: 'Koreksi odometer oleh Admin',
                isCorrection: true,
                correctionReason: $validated['correction_reason'],
            );
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['odometer' => $exception->getMessage()]);
        }

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('status', 'Odometer dikoreksi dan tercatat pada log.');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'departments' => Department::active()->latest()->get(),
            'rentalContracts' => RentalContract::active()->with('vendor')->orderBy('contract_number')->get(),
            'fuelTypes' => FuelType::options(),
            'ownerships' => Ownership::options(),
            'statuses' => VehicleStatus::options(),
        ];
    }
}
