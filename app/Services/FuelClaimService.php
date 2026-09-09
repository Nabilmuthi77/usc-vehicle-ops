<?php

namespace App\Services;

use App\Enums\FuelClaimStatus;
use App\Enums\OdometerSource;
use App\Exceptions\BusinessRuleException;
use App\Models\FuelTransaction;
use App\Models\User;
use App\Notifications\FuelClaimRejected;
use App\Notifications\FuelClaimSubmitted;
use App\Notifications\FuelClaimVerified;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Modul M3 — alur klaim reimbursement BBM (PRD §7.3).
 *
 * draft → diajukan → terverifikasi / ditolak → dibayar
 */
class FuelClaimService
{
    public function __construct(
        private readonly FuelConsumptionService $consumption,
        private readonly FuelAnomalyDetector $anomalyDetector,
        private readonly OdometerService $odometer,
        private readonly SettingService $settings,
        private readonly NotificationDispatcher $notifier,
    ) {}

    /**
     * FR-M3-01 s.d. FR-M3-05 — simpan transaksi pengisian BBM.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, User $actor): FuelTransaction
    {
        return DB::transaction(function () use ($data, $actor) {
            $transaction = new FuelTransaction($data);

            $transaction->fill([
                'claimant_id' => $data['claimant_id'] ?? $actor->getKey(),
                'created_by' => $actor->getKey(),
                // FR-M3-02 — total terisi otomatis namun tetap dapat dioverride.
                'total_cost' => $data['total_cost']
                    ?? round((float) $data['liters'] * (float) $data['price_per_liter'], 2),
                'status' => $data['status'] ?? FuelClaimStatus::Draft,
            ]);

            $transaction->is_late_claim = $this->isLateClaim($transaction->transaction_datetime);
            $transaction->save();

            $this->refreshDerivedValues($transaction, $actor);

            return $transaction->refresh();
        });
    }

    /**
     * Perbarui transaksi yang masih dapat disunting pengaju.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(FuelTransaction $transaction, array $data, User $actor): FuelTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $actor) {
            $transaction->fill($data);

            if (! isset($data['total_cost'])) {
                $transaction->total_cost = round(
                    (float) $transaction->liters * (float) $transaction->price_per_liter,
                    2,
                );
            }

            $transaction->is_late_claim = $this->isLateClaim($transaction->transaction_datetime);
            $transaction->save();

            $this->refreshDerivedValues($transaction, $actor);

            return $transaction->refresh();
        });
    }

    /**
     * FR-M3-06 & FR-M3-07 — pengajuan klaim; nota wajib terlampir.
     *
     * @throws BusinessRuleException
     */
    public function submitClaim(FuelTransaction $transaction, User $actor): FuelTransaction
    {
        if (! $transaction->status->isEditableByClaimant()) {
            throw new BusinessRuleException('Klaim ini sudah diajukan dan tidak dapat diajukan ulang.');
        }

        // FR-M3-07 — sistem menolak pengajuan tanpa lampiran nota.
        if (blank($transaction->receipt_photo_path)) {
            throw BusinessRuleException::rule(
                'FR-M3-07',
                'Foto nota wajib diunggah sebelum klaim dapat diajukan.',
            );
        }

        return DB::transaction(function () use ($transaction, $actor) {
            $transaction->fill([
                'status' => FuelClaimStatus::Diajukan,
                'submitted_at' => Carbon::now(),
                'rejection_reason' => null,
            ])->save();

            $this->notifier->sendToApprovers(new FuelClaimSubmitted($transaction));

            activity('FuelTransaction')
                ->performedOn($transaction)
                ->causedBy($actor)
                ->log('Klaim BBM diajukan');

            return $transaction;
        });
    }

    /**
     * FR-M3-09 — verifikasi klaim oleh Admin GA, dengan opsi koreksi nominal.
     *
     * @throws BusinessRuleException
     */
    public function verify(
        FuelTransaction $transaction,
        User $verifier,
        ?float $approvedAmount = null,
        ?string $correctionNote = null,
    ): FuelTransaction {
        if (! $transaction->isVerifiable()) {
            throw new BusinessRuleException('Hanya klaim berstatus diajukan yang dapat diverifikasi.');
        }

        if ($approvedAmount !== null && $approvedAmount != (float) $transaction->total_cost && blank($correctionNote)) {
            throw BusinessRuleException::rule(
                'FR-M3-09',
                'Koreksi nominal klaim wajib disertai catatan koreksi.',
            );
        }

        return DB::transaction(function () use ($transaction, $verifier, $approvedAmount, $correctionNote) {
            $transaction->fill([
                'status' => FuelClaimStatus::Terverifikasi,
                'approved_amount' => $approvedAmount ?? $transaction->total_cost,
                'correction_note' => $correctionNote,
                'verified_by' => $verifier->getKey(),
                'verified_at' => Carbon::now(),
                'rejection_reason' => null,
            ])->save();

            $this->notifier->send($transaction->claimant, new FuelClaimVerified($transaction));

            return $transaction;
        });
    }

    /** FR-M3-09 — penolakan klaim dengan alasan wajib. */
    public function reject(FuelTransaction $transaction, User $verifier, string $reason): FuelTransaction
    {
        if (! $transaction->isVerifiable()) {
            throw new BusinessRuleException('Hanya klaim berstatus diajukan yang dapat ditolak.');
        }

        return DB::transaction(function () use ($transaction, $verifier, $reason) {
            $transaction->fill([
                'status' => FuelClaimStatus::Ditolak,
                'rejection_reason' => $reason,
                'approved_amount' => null,
                'verified_by' => $verifier->getKey(),
                'verified_at' => Carbon::now(),
            ])->save();

            $this->notifier->send($transaction->claimant, new FuelClaimRejected($transaction));

            return $transaction;
        });
    }

    /**
     * FR-M3-08 & BR-15 — deteksi nota ganda.
     * Kombinasi SPBU + nomor nota + tanggal bersifat unik.
     */
    public function findDuplicate(
        string $stationName,
        string $receiptNumber,
        Carbon $transactionDatetime,
        ?int $exceptId = null,
    ): ?FuelTransaction {
        return FuelTransaction::query()
            ->where('station_name', $stationName)
            ->where('receipt_number', $receiptNumber)
            ->whereDate('transaction_datetime', $transactionDatetime->toDateString())
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->first();
    }

    /** FR-M3-14 — klaim melewati batas waktu sejak tanggal nota. */
    public function isLateClaim(Carbon $transactionDatetime): bool
    {
        $deadline = $this->settings->integer('claim_deadline_days', 30);

        if ($deadline <= 0) {
            return false;
        }

        return $transactionDatetime->copy()->addDays($deadline)->isPast();
    }

    /**
     * Hitung ulang konsumsi, anomali, dan catat odometer.
     * Dipanggil setiap kali data transaksi berubah.
     */
    private function refreshDerivedValues(FuelTransaction $transaction, User $actor): void
    {
        $this->consumption->recalculate($transaction);
        $this->anomalyDetector->evaluate($transaction->refresh());

        // FR-M4-03 — odometer kendaraan ikut terbarui dari pengisian BBM.
        // Pembacaan mundur di sini hanya ditandai anomali, bukan diblokir,
        // agar nota tetap dapat diklaim dan diperiksa Admin GA.
        if ((int) $transaction->odometer > (int) $transaction->vehicle->current_odometer) {
            $this->odometer->record(
                vehicle: $transaction->vehicle,
                odometer: (int) $transaction->odometer,
                source: OdometerSource::Fuel,
                recordedAt: $transaction->transaction_datetime,
                sourceable: $transaction,
                recordedBy: $actor,
                notes: 'Pengisian BBM nota '.$transaction->receipt_number,
            );
        }
    }
}
