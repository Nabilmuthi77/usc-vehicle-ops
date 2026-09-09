<?php

namespace App\Services;

use App\Enums\FuelClaimStatus;
use App\Enums\ReimbursementBatchStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\FuelTransaction;
use App\Models\ReimbursementBatch;
use App\Models\User;
use App\Notifications\ReimbursementPaid;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FR-M3-10 s.d. FR-M3-13 & FR-M3-18 — penggabungan klaim terverifikasi
 * ke dalam batch reimbursement per periode per pengaju.
 */
class ReimbursementService
{
    public function __construct(
        private readonly DocumentNumberService $numbers,
        private readonly NotificationDispatcher $notifier,
    ) {}

    /**
     * FR-M3-10 — susun batch dari seluruh klaim terverifikasi milik satu
     * pengaju pada rentang periode tertentu.
     *
     * @throws BusinessRuleException bila tidak ada klaim yang memenuhi syarat
     */
    public function buildBatch(
        User $claimant,
        Carbon $periodStart,
        Carbon $periodEnd,
        User $actor,
    ): ReimbursementBatch {
        $claims = $this->eligibleClaims($claimant, $periodStart, $periodEnd);

        if ($claims->isEmpty()) {
            throw new BusinessRuleException(
                'Tidak ada klaim terverifikasi yang belum masuk batch pada periode tersebut.',
            );
        }

        return DB::transaction(function () use ($claims, $claimant, $periodStart, $periodEnd, $actor) {
            $batch = ReimbursementBatch::create([
                'batch_number' => $this->numbers->generate(
                    'document_prefix_reimbursement_batch',
                    'reimbursement_batches',
                    'batch_number',
                ),
                'claimant_id' => $claimant->getKey(),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'status' => ReimbursementBatchStatus::Disusun,
                'created_by' => $actor->getKey(),
            ]);

            FuelTransaction::whereIn('id', $claims->modelKeys())
                ->update(['reimbursement_batch_id' => $batch->getKey()]);

            $batch->recalculateTotals();

            return $batch->refresh();
        });
    }

    /**
     * Klaim yang layak digabungkan: sudah terverifikasi dan belum terikat
     * batch mana pun (BR-14).
     *
     * @return Collection<int, FuelTransaction>
     */
    public function eligibleClaims(User $claimant, Carbon $periodStart, Carbon $periodEnd): Collection
    {
        return FuelTransaction::query()
            ->where('claimant_id', $claimant->getKey())
            ->where('status', FuelClaimStatus::Terverifikasi)
            ->whereNull('reimbursement_batch_id')
            ->whereDate('transaction_datetime', '>=', $periodStart)
            ->whereDate('transaction_datetime', '<=', $periodEnd)
            ->orderBy('transaction_datetime')
            ->get();
    }

    /** FR-M3-11 — batch diserahkan ke Finance beserta rekap PDF. */
    public function submitToFinance(ReimbursementBatch $batch, User $actor): ReimbursementBatch
    {
        if (! $batch->isOpen()) {
            throw new BusinessRuleException('Batch ini sudah diserahkan ke Finance.');
        }

        $batch->fill([
            'status' => ReimbursementBatchStatus::DiserahkanFinance,
            'submitted_at' => Carbon::now(),
        ])->save();

        activity('ReimbursementBatch')
            ->performedOn($batch)
            ->causedBy($actor)
            ->log('Batch reimbursement diserahkan ke Finance');

        $financeUsers = User::query()
            ->active()
            ->whereHas('department', fn ($q) => $q->where('name', 'Finance'))
            ->get();

        if ($financeUsers->isNotEmpty()) {
            $this->notifier->sendMany($financeUsers, new \App\Notifications\ReimbursementSubmittedToFinance($batch));
        }

        return $batch;
    }

    /**
     * FR-M3-12 — penandaan pembayaran oleh Finance; seluruh klaim di dalam
     * batch ikut berubah menjadi `dibayar`.
     *
     * @param  array<string, mixed>  $data
     */
    public function markAsPaid(ReimbursementBatch $batch, array $data, User $actor): ReimbursementBatch
    {
        if ($batch->status === ReimbursementBatchStatus::Dibayar) {
            throw new BusinessRuleException('Batch ini sudah ditandai dibayar.');
        }

        return DB::transaction(function () use ($batch, $data, $actor) {
            $batch->fill([
                'status' => ReimbursementBatchStatus::Dibayar,
                'paid_at' => $data['paid_at'] ?? Carbon::now(),
                'payment_proof_path' => $data['payment_proof_path'] ?? null,
                'payment_note' => $data['payment_note'] ?? null,
                'paid_by' => $actor->getKey(),
            ])->save();

            $batch->items()->update(['status' => FuelClaimStatus::Dibayar]);

            $this->notifier->send($batch->claimant, new ReimbursementPaid($batch));

            return $batch->refresh();
        });
    }

    /** Lepaskan sebuah klaim dari batch yang masih berstatus disusun. */
    public function removeClaim(ReimbursementBatch $batch, FuelTransaction $claim): void
    {
        if (! $batch->isOpen()) {
            throw new BusinessRuleException(
                'Klaim tidak dapat dilepas dari batch yang sudah diserahkan ke Finance.',
            );
        }

        $claim->forceFill(['reimbursement_batch_id' => null])->save();

        $batch->recalculateTotals();
        
        if ($batch->item_count === 0) {
            $batch->delete();
        }
    }

    /**
     * FR-M3-18 — laporan outstanding: klaim terverifikasi yang belum dibayar,
     * dikelompokkan per pengaju beserta umur klaimnya.
     *
     * @return array<int, array<string, mixed>>
     */
    public function outstandingReport(?int $departmentId = null, ?int $userId = null): array
    {
        return FuelTransaction::query()
            ->with('claimant')
            ->when($departmentId, fn ($q) => $q->whereHas('claimant', fn ($q) => $q->where('department_id', $departmentId)))
            ->when($userId, fn ($q) => $q->where('claimant_id', $userId))
            ->outstanding()
            ->whereNull('reimbursement_batch_id')
            ->get()
            ->groupBy('claimant_id')
            ->map(function (Collection $claims) {
                $oldest = $claims->min('transaction_datetime');

                return [
                    'claimant' => $claims->first()->claimant?->name,
                    'claimant_id' => $claims->first()->claimant_id,
                    'item_count' => $claims->count(),
                    'total_amount' => $claims->sum(fn (FuelTransaction $c) => $c->claimable_amount),
                    'oldest_claim_date' => $oldest,
                    'age_days' => $oldest ? (int) Carbon::parse($oldest)->diffInDays(Carbon::today()) : 0,
                ];
            })
            ->sortByDesc('total_amount')
            ->values()
            ->all();
    }
}
