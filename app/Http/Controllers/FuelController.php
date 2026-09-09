<?php

namespace App\Http\Controllers;

use App\Enums\FuelClaimStatus;
use App\Enums\FuelType;
use App\Enums\PaymentMethod;
use App\Exceptions\BusinessRuleException;
use App\Http\Requests\StoreFuelTransactionRequest;
use App\Http\Requests\VerifyFuelClaimRequest;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\FuelTransaction;
use App\Models\Vehicle;
use App\Services\FileUploadService;
use App\Services\FuelClaimService;
use App\Services\FuelConsumptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Modul M3 — pemakaian BBM & klaim reimbursement (PRD §7.3). */
class FuelController extends Controller
{
    public function __construct(
        private readonly FuelClaimService $claims,
        private readonly FuelConsumptionService $consumption,
        private readonly FileUploadService $uploads,
    ) {}

    /** FR-M3-17 — daftar transaksi BBM beserta ringkasan nominal. */
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', FuelTransaction::class);

        $user = $request->user();
        if (! $user->can(\App\Support\Permissions::FUEL_VIEW_ALL)) {
            return redirect()->route('fuel.my-claims');
        }

        $base = FuelTransaction::query()
            ->betweenDates($request->input('from'), $request->input('to'));

        $transactions = (clone $base)
            ->with(['vehicle', 'driver', 'claimant'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->boolean('anomaly'), fn ($q) => $q->where('is_anomaly', true))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->toString();
                $q->where(function ($query) use ($term) {
                    $query->where('station_name', 'like', "%{$term}%")
                        ->orWhere('receipt_number', 'like', "%{$term}%")
                        ->orWhere('transaction_datetime', 'like', "%{$term}%");
                    
                    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $term, $m)) {
                        $reversed = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
                        $query->orWhere('transaction_datetime', 'like', "%{$reversed}%");
                    }

                    $query->orWhereHas('vehicle', fn($q) => $q->where('plate_number', 'like', "%{$term}%"))
                        ->orWhereHas('driver', fn($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('claimant', fn($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('fuel.index', [
            'transactions' => $transactions,
            'summary' => $this->summarise(clone $base),
            'statuses' => FuelClaimStatus::options(),
            'vehicles' => Vehicle::orderBy('plate_number')->get(['id', 'plate_number']),
            'filters' => $request->only(['status', 'vehicle_id', 'from', 'to', 'anomaly', 'search']),
        ]);
    }

    /**
     * FR-M3-13 — halaman "Klaim Saya" untuk driver & karyawan.
     */
    public function myClaims(Request $request): View
    {
        $user = $request->user();

        $claims = FuelTransaction::query()
            ->with(['vehicle', 'batch'])
            ->where('claimant_id', $user->getKey())
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->toString();
                $q->where(function ($query) use ($term) {
                    $query->where('transaction_datetime', 'like', "%{$term}%")
                        ->orWhere('station_name', 'like', "%{$term}%")
                        ->orWhere('receipt_number', 'like', "%{$term}%")
                        ->orWhere('total_cost', 'like', "%{$term}%")
                        ->orWhere('status', 'like', "%{$term}%")
                        ->orWhereHas('vehicle', fn ($q) => $q->where('plate_number', 'like', "%{$term}%"))
                        ->orWhereHas('batch', fn ($q) => $q->where('batch_number', 'like', "%{$term}%"));
                        
                    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $term, $m)) {
                        $reversed = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
                        $query->orWhere('transaction_datetime', 'like', "%{$reversed}%");
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $owned = FuelTransaction::where('claimant_id', $user->getKey());

        return view('fuel.my-claims', [
            'claims' => $claims,
            'summary' => [
                'diajukan' => (clone $owned)->where('status', FuelClaimStatus::Diajukan)->sum('total_cost'),
                // BR-14 — terverifikasi namun belum dibayar = kewajiban perusahaan.
                'outstanding' => (clone $owned)->outstanding()
                    ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(approved_amount, total_cost)')),
                'dibayar' => (clone $owned)->where('status', FuelClaimStatus::Dibayar)
                    ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(approved_amount, total_cost)')),
                'ditolak' => (clone $owned)->where('status', FuelClaimStatus::Ditolak)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', FuelTransaction::class);

        return view('fuel.create', $this->formOptions());
    }

    /** FR-M3-01 s.d. FR-M3-05 — simpan transaksi pengisian BBM. */
    public function store(StoreFuelTransactionRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('receipt_photo');

        if ($request->hasFile('receipt_photo')) {
            $data['receipt_photo_path'] = $this->uploads->storeImage(
                $request->file('receipt_photo'),
                'receipts/fuel',
            );
        }

        $transaction = $this->claims->store($data, $request->user());

        $message = "Transaksi BBM nota {$transaction->receipt_number} tersimpan sebagai draft.";

        if ($transaction->is_anomaly) {
            $message .= ' Transaksi ditandai anomali: '
                .implode(' ', $transaction->anomaly_reason ?? []);
        }

        return redirect()->route('fuel.show', $transaction)->with('status', $message);
    }

    public function show(FuelTransaction $fuel): View
    {
        $this->authorize('view', $fuel);

        $fuel->load(['vehicle', 'driver', 'claimant', 'verifier', 'booking', 'batch']);

        return view('fuel.show', [
            'transaction' => $fuel,
            'previousFullTank' => $this->consumption->previousFullTank($fuel),
            'historicalAverage' => $this->consumption->historicalAverage($fuel->vehicle, $fuel),
        ]);
    }

    public function edit(FuelTransaction $fuel): View
    {
        $this->authorize('update', $fuel);

        return view('fuel.edit', $this->formOptions() + ['transaction' => $fuel]);
    }

    public function update(StoreFuelTransactionRequest $request, FuelTransaction $fuel): RedirectResponse
    {
        $data = $request->safe()->except('receipt_photo');

        if ($request->hasFile('receipt_photo')) {
            $data['receipt_photo_path'] = $this->uploads->replaceImage(
                $fuel->receipt_photo_path,
                $request->file('receipt_photo'),
                'receipts/fuel',
            );
        }

        $this->claims->update($fuel, $data, $request->user());

        return redirect()->route('fuel.show', $fuel)->with('status', 'Transaksi BBM diperbarui.');
    }

    /** FR-M3-06 & FR-M3-07 — ajukan klaim (nota wajib terlampir). */
    public function submitClaim(Request $request, FuelTransaction $fuel): RedirectResponse
    {
        $this->authorize('submitClaim', $fuel);

        try {
            $this->claims->submitClaim($fuel, $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['receipt_photo' => $exception->getMessage()]);
        }

        return redirect()
            ->route('fuel.show', $fuel)
            ->with('status', 'Klaim diajukan dan menunggu verifikasi Admin GA.');
    }

    /** FR-M3-09 — verifikasi klaim, dengan opsi koreksi nominal. */
    public function verify(VerifyFuelClaimRequest $request, FuelTransaction $fuel): RedirectResponse
    {
        try {
            $this->claims->verify(
                transaction: $fuel,
                verifier: $request->user(),
                approvedAmount: $request->filled('approved_amount')
                    ? (float) $request->input('approved_amount')
                    : null,
                correctionNote: $request->input('correction_note'),
            );
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['approved_amount' => $exception->getMessage()]);
        }

        return redirect()
            ->route('fuel.show', $fuel)
            ->with('status', 'Klaim terverifikasi dan siap digabung ke batch reimbursement.');
    }

    /** FR-M3-09 — penolakan klaim dengan alasan wajib. */
    public function reject(Request $request, FuelTransaction $fuel): RedirectResponse
    {
        $this->authorize('verify', $fuel);

        $validated = $request->validate(
            ['rejection_reason' => ['required', 'string', 'max:2000']],
            ['rejection_reason.required' => 'Alasan penolakan klaim wajib diisi.'],
        );

        try {
            $this->claims->reject($fuel, $request->user(), $validated['rejection_reason']);
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['rejection_reason' => $exception->getMessage()]);
        }

        return redirect()->route('fuel.show', $fuel)->with('status', 'Klaim ditolak.');
    }

    /** BR-11 — penghapusan memakai soft delete dan tercatat di audit log. */
    public function destroy(FuelTransaction $fuel): RedirectResponse
    {
        $this->authorize('delete', $fuel);

        $fuel->delete();

        return redirect()->route('fuel.index')->with('status', 'Transaksi BBM dihapus.');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'vehicles' => Vehicle::orderBy('plate_number')->get(),
            'drivers' => Driver::active()->latest()->get(),
            'bookings' => Booking::query()
                ->whereIn('status', [\App\Enums\BookingStatus::SedangDigunakan, \App\Enums\BookingStatus::Selesai])
                ->orderByDesc('booking_date')
                ->limit(100)
                ->get(),
            'fuelTypes' => FuelType::options(),
            'paymentMethods' => PaymentMethod::options(),
        ];
    }

    /**
     * Ringkasan nominal per status (FR-M3-17).
     *
     * @return array<string, float|int>
     */
    private function summarise($query): array
    {
        $amount = \Illuminate\Support\Facades\DB::raw('COALESCE(approved_amount, total_cost)');

        return [
            'diajukan' => (float) (clone $query)->where('status', FuelClaimStatus::Diajukan)->sum('total_cost'),
            'outstanding' => (float) (clone $query)->outstanding()->sum($amount),
            'dibayar' => (float) (clone $query)->where('status', FuelClaimStatus::Dibayar)->sum($amount),
            'anomali' => (clone $query)->where('is_anomaly', true)->count(),
        ];
    }
}
