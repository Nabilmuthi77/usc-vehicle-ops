<?php

namespace App\Http\Controllers;

use App\Enums\ServiceRequestStatus;
use App\Exceptions\BusinessRuleException;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\ServiceRequestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** FR-M4-15 s.d. FR-M4-21 — permintaan servis ke vendor kendaraan sewa. */
class ServiceRequestController extends Controller
{
    public function __construct(private readonly ServiceRequestService $requests) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        return view('service-requests.index', [
            'requests' => ServiceRequest::query()
                ->with(['vehicle', 'vendor', 'serviceType'])
                ->when($request->filled('search'), function ($q) use ($request) {
                    $search = $request->string('search');
                    $q->where(function ($q) use ($search) {
                        $q->where('request_number', 'like', "%{$search}%")
                          ->orWhere('status', 'like', "%{$search}%")
                          ->orWhere('sent_at', 'like', "%{$search}%")
                          ->orWhereHas('vehicle', fn ($q) => $q->where('plate_number', 'like', "%{$search}%"))
                          ->orWhereHas('vendor', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('serviceType', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    });
                })
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('vendor_id'), fn ($q) => $q->where('vendor_id', $request->integer('vendor_id')))
                ->when($request->boolean('open'), fn ($q) => $q->open())
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => ServiceRequestStatus::options(),
            'vendors' => Vendor::active()->latest()->get(['id', 'name']),
            // FR-M4-21 — dashboard evaluasi vendor sewa.
            'vendorPerformance' => $this->requests->vendorPerformance(),
            'filters' => $request->only(['search', 'status', 'vendor_id', 'open']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ServiceRequest::class);

        $vehicle = $request->filled('vehicle_id')
            ? Vehicle::find($request->integer('vehicle_id'))
            : null;

        return view('service-requests.create', [
            'vehicles' => Vehicle::orderBy('plate_number')->get(),
            'vendors' => Vendor::active()->latest()->get(),
            'serviceTypes' => ServiceType::active()->latest()->get(),
            'selectedVehicle' => $vehicle,
            // Prefill KM jatuh tempo dari jadwal paling mendesak.
            'dueSchedule' => $vehicle?->serviceSchedules()
                ->with('serviceType')
                ->needsAttention()
                ->first(),
        ]);
    }

    public function store(StoreServiceRequestRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::findOrFail($request->integer('vehicle_id'));

        $serviceRequest = $this->requests->create(
            $request->safe()->except('vehicle_id'),
            $vehicle,
            $request->user(),
        );

        return redirect()
            ->route('service-requests.show', $serviceRequest)
            ->with('status', "Permintaan servis {$serviceRequest->request_number} dibuat.");
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        $this->authorize('view', $serviceRequest);

        $serviceRequest->load(['vehicle.rentalContract', 'vendor', 'serviceType', 'serviceRecord', 'createdBy']);

        return view('service-requests.show', [
            'request' => $serviceRequest,
            'allowedTransitions' => array_filter(
                $serviceRequest->status->allowedTransitions(),
                fn ($s) => $s !== ServiceRequestStatus::Selesai
            ),
            'daysSinceSent' => $serviceRequest->daysSinceSent(),
        ]);
    }

    /** FR-M4-17 — kirim permintaan ke PIC vendor via email. */
    public function send(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorize('send', $serviceRequest);

        $validated = $request->validate([
            'sent_to_email' => ['required', 'email'],
        ]);

        try {
            $this->requests->sendToVendor($serviceRequest, $validated['sent_to_email'], $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['sent_to_email' => $exception->getMessage()]);
        }

        return back()->with('status', 'Permintaan servis dikirim ke '.$validated['sent_to_email'].'.');
    }

    /** FR-M4-16 — perubahan status mengikuti transisi yang diizinkan. */
    public function updateStatus(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorize('update', $serviceRequest);

        $validated = $request->validate([
            'status' => ['required', ServiceRequestStatus::rule()],
            'status_date' => ['nullable', 'date'],
            'vendor_response_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === ServiceRequestStatus::Selesai->value) {
            return back()->withErrors(['status' => 'Status Selesai harus diproses melalui form penyelesaian (yang mencatat biaya & odometer).']);
        }

        if ($validated['status'] === ServiceRequestStatus::Dijadwalkan->value) {
            $statusDate = $validated['status_date'] ? \Illuminate\Support\Carbon::parse($validated['status_date']) : null;
            if ($statusDate && $serviceRequest->sent_at && $statusDate->isBefore($serviceRequest->sent_at->startOfDay())) {
                return back()->withInput()->withErrors([
                    'status_date' => 'Tanggal dijadwalkan tidak boleh lebih awal dari tanggal dikirim (' . $serviceRequest->sent_at->format('d-m-Y') . ').'
                ]);
            }
        }

        try {
            $this->requests->transitionTo(
                $serviceRequest,
                ServiceRequestStatus::from($validated['status']),
                $validated,
            );
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Status permintaan servis diperbarui.');
    }

    /** FR-M4-19 — penyelesaian permintaan; biaya bersifat opsional. */
    public function complete(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorize('complete', $serviceRequest);

        $validated = $request->validate([
            'completed_date' => ['required', 'date'],
            'odometer' => ['required', 'integer', 'min:0'],
            'invoice_number' => ['nullable', 'string', 'max:60'],
            // Biaya hanya informasi bila vendor memberi rincian.
            'total_cost' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'vendor_response_note' => ['nullable', 'string', 'max:2000'],
            'downtime_days' => ['nullable', 'integer', 'min:0'],
        ], [
            'completed_date.required' => 'Tanggal selesai wajib diisi.',
            'completed_date.date' => 'Format tanggal selesai tidak valid.',
        ]);

        $completedDate = \Illuminate\Support\Carbon::parse($validated['completed_date']);
        
        if ($serviceRequest->sent_at && $completedDate->isBefore($serviceRequest->sent_at->startOfDay())) {
            return back()->withInput()->withErrors([
                'completed_date' => 'Tanggal selesai tidak boleh lebih awal dari tanggal dikirim (' . $serviceRequest->sent_at->format('d-m-Y') . ').'
            ]);
        }
        
        if ($serviceRequest->scheduled_date && $completedDate->isBefore($serviceRequest->scheduled_date->startOfDay())) {
            return back()->withInput()->withErrors([
                'completed_date' => 'Tanggal selesai tidak boleh lebih awal dari tanggal dijadwalkan (' . $serviceRequest->scheduled_date->format('d-m-Y') . ').'
            ]);
        }

        try {
            $record = $this->requests->complete($serviceRequest, $validated, $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->withInput()->withErrors(['completed_date' => $exception->getMessage()]);
        }

        return redirect()
            ->route('service-requests.show', $serviceRequest)
            ->with('status', 'Permintaan servis diselesaikan. Riwayat servis #'
                .$record->getKey().' tercatat dan jadwal berikutnya diperbarui.');
    }

    /** FR-M4-17 — cetak/export PDF permintaan servis. */
    public function print(ServiceRequest $serviceRequest)
    {
        $this->authorize('view', $serviceRequest);

        $pdf = Pdf::loadView('pdf.service-request', [
            'request' => $serviceRequest->load(['vehicle', 'vendor', 'serviceType', 'createdBy']),
            'company' => config('usc_vehicle_ops.company'),
        ]);

        return $pdf->stream(str_replace('/', '-', $serviceRequest->request_number).'.pdf');
    }
}
