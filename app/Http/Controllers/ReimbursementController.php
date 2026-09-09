<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Models\ReimbursementBatch;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\ReimbursementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** FR-M3-10 s.d. FR-M3-12 — batch reimbursement klaim BBM. */
class ReimbursementController extends Controller
{
    public function __construct(
        private readonly ReimbursementService $reimbursements,
        private readonly FileUploadService $uploads,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ReimbursementBatch::class);

        $user = $request->user();

        $batches = ReimbursementBatch::query()
            ->with(['claimant', 'paidBy'])
            ->when($user->seesOnlyOwnRecords(), fn ($q) => $q->where('claimant_id', $user->getKey()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q) use ($search) {
                    $q->where('batch_number', 'like', "%{$search}%")
                      ->orWhereHas('claimant', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('reimbursements.index', [
            'batches' => $batches,
            // FR-M3-18 — outstanding per pengaju.
            'outstanding' => $this->reimbursements->outstandingReport(null, $user->seesOnlyOwnRecords() ? $user->getKey() : null),
            'statuses' => \App\Enums\ReimbursementBatchStatus::options(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ReimbursementBatch::class);

        $claimantId = $request->integer('claimant_id');
        $periodStart = Carbon::parse($request->input('period_start', Carbon::today()->startOfMonth()));
        $periodEnd = Carbon::parse($request->input('period_end', Carbon::today()->endOfMonth()));

        return view('reimbursements.create', [
            'claimants' => User::query()
                ->whereHas('fuelClaims', fn ($q) => $q->outstanding()->whereNull('reimbursement_batch_id'))
                ->orderBy('name')
                ->get(),
            'selectedClaimant' => $claimantId ? User::find($claimantId) : null,
            'eligibleClaims' => $claimantId
                ? $this->reimbursements->eligibleClaims(User::findOrFail($claimantId), $periodStart, $periodEnd)
                : collect(),
            'periodStart' => $periodStart->toDateString(),
            'periodEnd' => $periodEnd->toDateString(),
        ]);
    }

    /** FR-M3-10 — susun batch dari klaim terverifikasi pada satu periode. */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ReimbursementBatch::class);

        $validated = $request->validate([
            'claimant_id' => ['required', 'exists:users,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        try {
            $batch = $this->reimbursements->buildBatch(
                claimant: User::findOrFail($validated['claimant_id']),
                periodStart: Carbon::parse($validated['period_start']),
                periodEnd: Carbon::parse($validated['period_end']),
                actor: $request->user(),
            );
        } catch (BusinessRuleException $exception) {
            return back()->withInput()->withErrors(['claimant_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('reimbursements.show', $batch)
            ->with('status', "Batch {$batch->batch_number} dibuat dengan {$batch->item_count} klaim.");
    }

    public function show(ReimbursementBatch $reimbursement): View
    {
        $this->authorize('view', $reimbursement);

        $reimbursement->load(['claimant.department', 'items.vehicle', 'paidBy']);

        return view('reimbursements.show', ['batch' => $reimbursement]);
    }

    /** FR-M3-11 — cetak rekap klaim untuk diserahkan ke Finance. */
    public function print(ReimbursementBatch $reimbursement)
    {
        $this->authorize('view', $reimbursement);

        $reimbursement->load(['claimant.department', 'items.vehicle', 'createdBy', 'paidBy']);

        $pdf = Pdf::loadView('pdf.reimbursement-batch', [
            'batch' => $reimbursement,
            'company' => config('usc_vehicle_ops.company'),
            'terbilang' => $this->spellOut((int) $reimbursement->total_amount),
        ]);

        return $pdf->stream(str_replace('/', '-', $reimbursement->batch_number).'.pdf');
    }

    public function submitToFinance(Request $request, ReimbursementBatch $reimbursement): RedirectResponse
    {
        $this->authorize('update', $reimbursement);

        try {
            $this->reimbursements->submitToFinance($reimbursement, $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Batch diserahkan ke Finance.');
    }

    /** FR-M3-12 — penandaan pembayaran beserta bukti transfer. */
    public function markAsPaid(Request $request, ReimbursementBatch $reimbursement): RedirectResponse
    {
        $this->authorize('markAsPaid', $reimbursement);

        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.document_mimes', ['jpg', 'png', 'pdf']));

        $validated = $request->validate([
            'paid_at' => ['required', 'date', 'before_or_equal:now'],
            'payment_proof' => ['nullable', 'file', $mimes, 'max:'.$maxSize],
            'payment_note' => ['nullable', 'string', 'max:2000'],
        ]);

        unset($validated['payment_proof']);

        if ($request->hasFile('payment_proof')) {
            $validated['payment_proof_path'] = $this->uploads->storeImage(
                $request->file('payment_proof'),
                'reimbursements/proofs',
            );
        }

        try {
            $this->reimbursements->markAsPaid($reimbursement, $validated, $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['paid_at' => $exception->getMessage()]);
        }

        return back()->with('status', 'Batch ditandai sudah dibayar dan seluruh klaim diperbarui.');
    }

    public function removeClaim(ReimbursementBatch $reimbursement, \App\Models\FuelTransaction $claim): RedirectResponse
    {
        $this->authorize('update', $reimbursement);

        try {
            $this->reimbursements->removeClaim($reimbursement, $claim);
        } catch (BusinessRuleException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        if (!$reimbursement->exists || $reimbursement->item_count === 0) {
            return redirect()->route('reimbursements.index')->with('status', 'Klaim terakhir dilepas, batch otomatis dihapus.');
        }

        return back()->with('status', 'Klaim dilepas dari batch.');
    }

    /**
     * Terbilang Rupiah untuk dokumen rekap (FR-M3-11).
     */
    private function spellOut(int $number): string
    {
        $units = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return trim($units[$number] ?: 'nol');
        }

        if ($number < 20) {
            return trim($this->spellOut($number - 10).' belas');
        }

        if ($number < 100) {
            return trim($this->spellOut(intdiv($number, 10)).' puluh '.$this->spellOut($number % 10));
        }

        if ($number < 200) {
            return trim('seratus '.$this->spellOut($number - 100));
        }

        if ($number < 1000) {
            return trim($this->spellOut(intdiv($number, 100)).' ratus '.$this->spellOut($number % 100));
        }

        if ($number < 2000) {
            return trim('seribu '.$this->spellOut($number - 1000));
        }

        if ($number < 1000000) {
            return trim($this->spellOut(intdiv($number, 1000)).' ribu '.$this->spellOut($number % 1000));
        }

        if ($number < 1000000000) {
            return trim($this->spellOut(intdiv($number, 1000000)).' juta '.$this->spellOut($number % 1000000));
        }

        return trim($this->spellOut(intdiv($number, 1000000000)).' miliar '.$this->spellOut($number % 1000000000));
    }
}
