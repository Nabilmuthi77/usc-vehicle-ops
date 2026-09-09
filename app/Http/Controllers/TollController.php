<?php

namespace App\Http\Controllers;

use App\Enums\VehicleClass;
use App\Exceptions\BusinessRuleException;
use App\Http\Requests\StoreTollTransactionRequest;
use App\Models\Booking;
use App\Models\TollCard;
use App\Models\TollRate;
use App\Models\TollTransaction;
use App\Models\Vehicle;
use App\Services\FileUploadService;
use App\Services\TollBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Modul M5 — biaya tol & saldo kartu e-toll (PRD §7.5). */
class TollController extends Controller
{
    public function __construct(
        private readonly TollBalanceService $tollBalance,
        private readonly FileUploadService $uploads,
    ) {}

    /** FR-M5-01 & FR-M5-09 — kartu e-toll beserta transaksinya. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TollTransaction::class);

        $transactions = TollTransaction::query()
            ->with(['vehicle', 'tollCard', 'booking'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->input('q');
                $q->where(function ($sub) use ($search) {
                    $sub->where('entry_gate', 'like', "%{$search}%")
                        ->orWhere('exit_gate', 'like', "%{$search}%")
                        ->orWhere('road_section', 'like', "%{$search}%")
                        ->orWhere('vehicle_class', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('transaction_datetime', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', fn($v) => $v->where('plate_number', 'like', "%{$search}%"))
                        ->orWhereHas('tollCard', fn($c) => $c->where('issuer', 'like', "%{$search}%")->orWhere('card_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('toll_card_id'), fn ($q) => $q->where('toll_card_id', $request->integer('toll_card_id')))
            ->betweenDates($request->input('from'), $request->input('to'))
            ->latest()
            ->latest('id')
            ->paginate(5, ['*'], 'trans_page')
            ->withQueryString()
            ->appends(['tab' => 'transactions']);

        $topups = \App\Models\TollTopup::with(['tollCard.vehicle', 'createdBy'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->input('q');
                $q->where(function ($sub) use ($search) {
                    $sub->where('method', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('topup_date', 'like', "%{$search}%")
                        ->orWhereHas('tollCard', fn($c) => $c->where('issuer', 'like', "%{$search}%")
                            ->orWhere('card_number', 'like', "%{$search}%")
                            ->orWhereHas('vehicle', fn($v) => $v->where('plate_number', 'like', "%{$search}%"))
                        )
                        ->orWhereHas('createdBy', fn($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('toll_card_id'), fn ($q) => $q->where('toll_card_id', $request->integer('toll_card_id')))
            ->latest()
            ->latest('id')
            ->paginate(5, ['*'], 'topup_page')
            ->withQueryString()
            ->appends(['tab' => 'topups']);

        $adjustments = \App\Models\TollAdjustment::with(['tollCard.vehicle', 'createdBy'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->input('q');
                $q->where(function ($sub) use ($search) {
                    $sub->where('reason', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('system_balance', 'like', "%{$search}%")
                        ->orWhere('actual_balance', 'like', "%{$search}%")
                        ->orWhere('adjustment_date', 'like', "%{$search}%")
                        ->orWhereHas('tollCard', fn($c) => $c->where('issuer', 'like', "%{$search}%")
                            ->orWhere('card_number', 'like', "%{$search}%")
                        )
                        ->orWhereHas('createdBy', fn($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('toll_card_id'), fn ($q) => $q->where('toll_card_id', $request->integer('toll_card_id')))
            ->latest()
            ->latest('id')
            ->paginate(5, ['*'], 'adj_page')
            ->withQueryString()
            ->appends(['tab' => 'adjustments']);

        return view('toll.index', [
            'cards' => TollCard::with('vehicle')->orderBy('issuer')->get(),
            'transactions' => $transactions,
            'topups' => $topups,
            'adjustments' => $adjustments,
            'vehicles' => Vehicle::orderBy('plate_number')->get(['id', 'plate_number']),
            'filters' => $request->only(['vehicle_id', 'toll_card_id', 'from', 'to', 'q']),
            'vehicleClasses' => VehicleClass::options(),
            'activeBookings' => $this->activeBookings(),
        ]);
    }

    /** FR-M5-03 — pencatatan transaksi tol; saldo berkurang otomatis. */
    public function storeTransaction(StoreTollTransactionRequest $request): RedirectResponse
    {
        $card = TollCard::findOrFail($request->integer('toll_card_id'));

        $this->tollBalance->recordTransaction(
            $card,
            $request->safe()->except('toll_card_id'),
            $request->user(),
        );

        return redirect()
            ->route('toll.index')
            ->with('status', 'Transaksi tol tercatat. Saldo kartu diperbarui otomatis.');
    }

    /** FR-M5-02 — pencatatan top-up saldo. */
    public function storeTopup(Request $request): RedirectResponse
    {
        $this->authorize('create', TollTransaction::class);

        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.document_mimes', ['jpg', 'png', 'pdf']));

        $validated = $request->validateWithBag('topup', [
            'toll_card_id' => ['required', 'exists:toll_cards,id'],
            'topup_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['nullable', 'string', 'max:40'],
            'reference_number' => ['nullable', 'string', 'max:60'],
            'proof' => ['nullable', 'file', $mimes, 'max:'.$maxSize],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'toll_card_id' => 'pilihan kartu',
            'topup_date' => 'tanggal top-up',
            'amount' => 'nominal top-up',
            'method' => 'metode',
            'reference_number' => 'nomor referensi',
            'proof' => 'bukti',
            'notes' => 'catatan',
        ]);

        unset($validated['proof']);

        if ($request->hasFile('proof')) {
            $validated['proof_path'] = $this->uploads->storeImage(
                $request->file('proof'),
                'toll/topups',
            );
        }

        $card = TollCard::findOrFail($validated['toll_card_id']);

        $this->tollBalance->recordTopup($card, $validated, $request->user());

        return redirect()
            ->route('toll.index')
            ->with('status', 'Top-up tercatat. Saldo kartu bertambah otomatis.');
    }

    /** FR-M5-08 — rekonsiliasi saldo sistem vs saldo aktual kartu. */
    public function reconcile(Request $request): RedirectResponse
    {
        $this->authorize('create', TollTransaction::class);

        $validated = $request->validateWithBag(
            'reconcile',
            [
                'toll_card_id' => ['required', 'exists:toll_cards,id'],
                'actual_balance' => ['required', 'numeric', 'min:0'],
                'reason' => ['required', 'string', 'max:2000'],
            ],
            ['reason.required' => 'Alasan penyesuaian saldo wajib diisi.'],
            [
                'toll_card_id' => 'pilihan kartu',
                'actual_balance' => 'saldo aktual',
                'reason' => 'alasan',
            ]
        );

        try {
            $card = TollCard::findOrFail($validated['toll_card_id']);
            $adjustment = $this->tollBalance->reconcile(
                card: $card,
                actualBalance: (float) $validated['actual_balance'],
                reason: $validated['reason'],
                actor: $request->user(),
            );
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['reason' => $exception->getMessage()]);
        }

        return redirect()->route('toll.index')->with('status', sprintf(
            'Rekonsiliasi tersimpan. Selisih Rp %s dicatat sebagai penyesuaian.',
            number_format((float) $adjustment->amount, 0, ',', '.'),
        ));
    }

    /**
     * FR-M5-05 — tarif otomatis berdasarkan kombinasi gerbang & golongan.
     * Dipakai form transaksi tol melalui permintaan AJAX.
     */
    public function lookupRate(Request $request)
    {
        $validated = $request->validate([
            'entry_gate' => ['required', 'string'],
            'exit_gate' => ['required', 'string'],
            'vehicle_class' => ['required', VehicleClass::rule()],
        ]);

        $rate = TollRate::lookup(
            $validated['entry_gate'],
            $validated['exit_gate'],
            VehicleClass::from($validated['vehicle_class']),
        );

        return response()->json([
            'found' => $rate !== null,
            'amount' => $rate?->amount,
            'road_section' => $rate?->road_section,
        ]);
    }

    public function storeCard(Request $request): RedirectResponse
    {
        $this->authorize('create', TollTransaction::class);

        $validated = $request->validateWithBag('storeCard', [
            'card_number' => ['required', 'string', 'max:50', 'unique:toll_cards,card_number'],
            'issuer' => ['required', 'string', 'max:100'],
            'balance' => ['required', 'numeric', 'min:0'],
            'min_balance_alert' => ['required', 'numeric', 'min:0'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id', 'unique:toll_cards,vehicle_id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'vehicle_id.unique' => 'Kendaraan ini sudah memiliki kartu E-Toll. Satu kendaraan maksimal hanya boleh ditempel 1 kartu.',
            'card_number.unique' => 'Nomor kartu ini sudah terdaftar di sistem.',
        ], [
            'card_number' => 'nomor kartu',
            'issuer' => 'penerbit',
            'balance' => 'saldo awal',
            'min_balance_alert' => 'peringatan saldo minimum',
            'vehicle_id' => 'kendaraan',
            'notes' => 'catatan',
        ]);

        $validated['status'] = \App\Enums\TollCardStatus::Aktif;
        $validated['is_active'] = true;

        $card = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request) {
            $card = TollCard::create($validated);

            if ((float) $card->balance > 0) {
                $this->tollBalance->recordTopup($card, [
                    'topup_date' => now()->toDateString(),
                    'amount' => $card->balance,
                    'method' => 'Saldo Awal',
                    'notes' => 'Saldo awal saat registrasi kartu fisik.',
                ], $request->user());
            }
            
            return $card;
        });

        $statusMsg = 'Kartu E-Toll baru berhasil ditambahkan.';
        if ((float) $validated['balance'] < (float) $validated['min_balance_alert']) {
            $statusMsg .= ' Peringatan: Saldo saat ini di bawah batas minimum, harap pertimbangkan untuk melakukan top-up.';

            \Illuminate\Support\Facades\Notification::send(
                \App\Models\User::approvers()->get(),
                new \App\Notifications\TollBalanceLow($card)
            );
        }

        return redirect()->route('toll.index')->with('status', $statusMsg);
    }

    public function updateCard(Request $request, TollCard $card): RedirectResponse
    {
        $this->authorize('create', TollTransaction::class);

        $validated = $request->validateWithBag('updateCard', [
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\TollCardStatus::class)],
            'vehicle_id' => ['nullable', 'exists:vehicles,id', \Illuminate\Validation\Rule::unique('toll_cards', 'vehicle_id')->ignore($card->id)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'vehicle_id.unique' => 'Kendaraan ini sudah memiliki kartu E-Toll. Satu kendaraan maksimal hanya boleh ditempel 1 kartu.',
        ], [
            'status' => 'status kartu',
            'vehicle_id' => 'kendaraan',
            'notes' => 'catatan',
        ]);

        $validated['is_active'] = $validated['status'] === \App\Enums\TollCardStatus::Aktif->value;

        $card->update($validated);

        return redirect()->route('toll.index')->with('status', 'Data kartu E-Toll berhasil diperbarui.');
    }

    public function destroyTransaction(TollTransaction $tollTransaction): RedirectResponse
    {
        $this->authorize('delete', $tollTransaction);

        $this->tollBalance->deleteTransaction($tollTransaction);

        return redirect()
            ->route('toll.index')
            ->with('status', 'Transaksi tol dihapus dan saldo dihitung ulang.');
    }

    /** Daftar peminjaman aktif untuk pengaitan biaya (FR-M5-04). */
    public function activeBookings()
    {
        return Booking::query()
            ->with('vehicle')
            ->whereIn('status', [\App\Enums\BookingStatus::SedangDigunakan, \App\Enums\BookingStatus::Selesai])
            ->orderByDesc('booking_date')
            ->limit(100)
            ->get(['id', 'booking_number', 'vehicle_id', 'booking_date']);
    }
}
