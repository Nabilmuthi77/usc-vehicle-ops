<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\DurationType;
use App\Exceptions\BusinessRuleException;
use App\Http\Requests\ApproveBookingRequest;
use App\Http\Requests\BookingInspectionRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\BookingService;
use App\Services\FileUploadService;
use App\Services\VehicleAvailabilityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Modul M2 — peminjaman kendaraan (PRD §7.2). */
class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly VehicleAvailabilityService $availability,
        private readonly FileUploadService $uploads,
    ) {}

    /**
     * FR-M2-27 — daftar peminjaman dengan filter & pencarian.
     *
     * FR-M6-06 — Karyawan & Driver hanya melihat aktivitasnya sendiri.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Booking::class);

        $user = $request->user();

        $bookings = Booking::query()
            ->with(['requester', 'vehicle', 'driver', 'department'])
            ->when($user->seesOnlyOwnRecords(), fn ($q) => $q->ownedBy($user))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->toString();
                $q->where(function ($query) use ($term) {
                    $query->where('booking_number', 'like', "%{$term}%")
                        ->orWhere('destination', 'like', "%{$term}%")
                        ->orWhere('purpose', 'like', "%{$term}%")
                        ->orWhere('duration_type', 'like', '%' . str_replace([' ', '-'], '_', $term) . '%')
                        ->orWhereHas('requester', fn($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('department', fn($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('vehicle', fn($q) => $q->where('plate_number', 'like', "%{$term}%"))
                        ->orWhereHas('driver', fn($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->betweenDates($request->input('from'), $request->input('to'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bookings.index', [
            'bookings' => $bookings,
            'statuses' => BookingStatus::options(),
            'durations' => DurationType::options(),
            'vehicles' => Vehicle::orderBy('plate_number')->get(['id', 'plate_number']),
            'filters' => $request->only(['status', 'vehicle_id', 'search', 'from', 'to']),
        ]);
    }

    /**
     * FR-M2-16 — antrean approval Admin GA dalam satu halaman, terurut
     * dari tanggal pemakaian terdekat.
     */
    public function approvalQueue(): View
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = Booking::query()
            ->with(['requester', 'department'])
            ->pendingApproval()
            ->orderBy('booking_date')
            ->orderByDesc('is_urgent')
            ->get();

        return view('bookings.approval-queue', ['bookings' => $bookings]);
    }

    public function create(): View
    {
        $this->authorize('create', Booking::class);

        return view('bookings.create', [
            'durations' => DurationType::options(),
        ]);
    }

    /** FR-M2-01 — pengajuan 5 field oleh pemohon. */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookings->submit($request->validatedData(), $request->user());

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', "Pengajuan {$booking->booking_number} berhasil dikirim dan menunggu persetujuan Admin GA.");
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load([
            'requester', 'department', 'vehicle', 'driver', 'approver',
            'checkoutInspection.photos', 'checkinInspection.photos',
            'fuelTransactions', 'tollTransactions',
        ]);

        return view('bookings.show', [
            'booking' => $booking,
            // FR-M2-11 s.d. FR-M2-14 — layar penugasan Admin GA.
            'candidates' => $booking->status === BookingStatus::MenungguApproval
                ? $this->availability->candidatesFor($booking)
                : collect(),
            'drivers' => $booking->isAssignmentEditable()
                ? $this->availability->availableDrivers($booking)
                : collect(),
        ]);
    }

    /** FR-M2-09 s.d. FR-M2-14 — persetujuan & penugasan unit. */
    public function approve(ApproveBookingRequest $request, Booking $booking): RedirectResponse
    {
        $vehicle = Vehicle::findOrFail($request->integer('vehicle_id'));
        $driver = $request->filled('driver_id')
            ? Driver::findOrFail($request->integer('driver_id'))
            : null;

        try {
            $this->bookings->approve(
                booking: $booking,
                vehicle: $vehicle,
                driver: $driver,
                approver: $request->user(),
                assignmentNote: $request->input('assignment_note'),
            );
        } catch (BusinessRuleException $exception) {
            return back()->withInput()->withErrors(['vehicle_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', "Pengajuan {$booking->booking_number} disetujui dengan unit {$vehicle->plate_number}.");
    }

    /** FR-M2-15 — penolakan dengan alasan wajib. */
    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('reject', $booking);

        $validated = $request->validate(
            ['rejection_reason' => ['required', 'string', 'max:2000']],
            ['rejection_reason.required' => 'Alasan penolakan wajib diisi.'],
        );

        try {
            $this->bookings->reject($booking, $validated['rejection_reason'], $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['rejection_reason' => $exception->getMessage()]);
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', "Pengajuan {$booking->booking_number} ditolak.");
    }

    /** FR-M2-07 — pembatalan oleh pemohon sebelum serah terima. */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->bookings->cancel($booking, $validated['cancellation_reason'] ?? null, $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['cancellation_reason' => $exception->getMessage()]);
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', "Pengajuan {$booking->booking_number} dibatalkan.");
    }

    /** FR-M2-20 — serah terima kendaraan. */
    public function checkOut(BookingInspectionRequest $request, Booking $booking): RedirectResponse
    {
        try {
            $this->bookings->checkOut(
                $booking,
                $this->inspectionPayload($request, $booking),
                $request->user(),
            );
        } catch (BusinessRuleException $exception) {
            return back()->withInput()->withErrors(['odometer' => $exception->getMessage()]);
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'Serah terima tercatat. Kendaraan berstatus sedang digunakan.');
    }

    /** FR-M2-21 s.d. FR-M2-23 — pengembalian kendaraan. */
    public function checkIn(BookingInspectionRequest $request, Booking $booking): RedirectResponse
    {
        try {
            $this->bookings->checkIn(
                $booking,
                $this->inspectionPayload($request, $booking),
                $request->user(),
            );
        } catch (BusinessRuleException $exception) {
            return back()->withInput()->withErrors(['odometer' => $exception->getMessage()]);
        }

        $booking->refresh();

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'Pengembalian tercatat. Jarak tempuh '
                .number_format((int) $booking->distance_traveled, 0, ',', '.').' km.')
            // FR-M2-23 — peringatan jarak tidak wajar tidak memblokir penyimpanan.
            ->with('warning', $this->bookings->unusualDistanceWarning($booking));
    }

    /**
     * FR-M2-19 — papan penugasan harian untuk dicetak atau ditampilkan
     * di ruang GA.
     */
    public function assignmentBoard(Request $request): View
    {
        // Papan tugas memuat penugasan seluruh pengguna, sehingga dibatasi
        // pada peran yang memang boleh melihat semua peminjaman (FR-M6-06).
        abort_unless($request->user()->can(\App\Support\Permissions::BOOKING_VIEW_ALL), 403);

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        return view('bookings.assignment-board', [
            'date' => $date,
            'assignments' => $this->bookings->dailyAssignmentBoard($date),
        ]);
    }

    /** FR-M2-26 — cetak surat jalan / form serah terima dalam PDF. */
    public function printHandover(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load([
            'requester', 'department', 'vehicle', 'driver', 'approver',
            'checkoutInspection', 'checkinInspection',
        ]);

        $pdf = Pdf::loadView('pdf.booking-handover', [
            'booking' => $booking,
            'company' => config('usc_vehicle_ops.company'),
            'checkoutChecklist' => $this->formatChecklist($booking->checkoutInspection?->checklist),
            'checkinChecklist' => $this->formatChecklist($booking->checkinInspection?->checklist),
        ]);

        return $pdf->stream(str_replace('/', '-', $booking->booking_number).'.pdf');
    }

    /**
     * Susun payload inspeksi termasuk menyimpan foto kondisi.
     *
     * @return array<string, mixed>
     */
    private function inspectionPayload(BookingInspectionRequest $request, Booking $booking): array
    {
        $data = $request->safe()->except('photos');
        $photos = [];

        foreach ($request->file('photos', []) as $position => $file) {
            $photos[$position] = $this->uploads->storeImage(
                $file,
                'inspections/'.$booking->getKey(),
            );
        }

        $data['photos'] = $photos;

        return $data;
    }

    /** Ubah checklist JSON menjadi daftar terbaca untuk PDF. */
    private function formatChecklist(?array $checklist): string
    {
        if (blank($checklist)) {
            return '';
        }

        return collect($checklist)
            ->filter()
            ->keys()
            ->map(fn (string $item) => str_replace('_', ' ', $item))
            ->implode(', ');
    }
}
