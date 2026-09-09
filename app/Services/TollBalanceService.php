<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\TollAdjustment;
use App\Models\TollCard;
use App\Models\TollTopup;
use App\Models\TollTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Modul M5 — pengelolaan saldo kartu e-toll.
 *
 * BR-08 — `saldo = Σ top-up − Σ transaksi ± Σ penyesuaian`.
 * Saldo pada tabel `toll_cards` adalah nilai turunan yang selalu dihitung
 * ulang dari ketiga sumber tersebut agar tidak pernah menyimpang.
 */
class TollBalanceService
{
    public function recalculate(TollCard $card): float
    {
        $oldBalance = (float) $card->balance;

        $topups = (float) TollTopup::where('toll_card_id', $card->getKey())->sum('amount');
        $spending = (float) TollTransaction::where('toll_card_id', $card->getKey())->sum('amount');
        $adjustments = (float) TollAdjustment::where('toll_card_id', $card->getKey())->sum('amount');

        $balance = round($topups - $spending + $adjustments, 2);

        $card->forceFill(['balance' => $balance])->save();

        if ($balance < $oldBalance && $balance < (float) $card->min_balance_alert) {
            \Illuminate\Support\Facades\Notification::send(
                \App\Models\User::approvers()->get(),
                new \App\Notifications\TollBalanceLow($card)
            );
        }

        return $balance;
    }

    /**
     * FR-M5-02 — pencatatan top-up; saldo bertambah otomatis.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordTopup(TollCard $card, array $data, User $actor): TollTopup
    {
        return DB::transaction(function () use ($card, $data, $actor) {
            $topup = TollTopup::create([
                ...$data,
                'toll_card_id' => $card->getKey(),
                'created_by' => $actor->getKey(),
            ]);

            $this->recalculate($card);

            return $topup;
        });
    }

    /**
     * FR-M5-03 — pencatatan transaksi tol; saldo berkurang otomatis.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordTransaction(TollCard $card, array $data, User $actor): TollTransaction
    {
        return DB::transaction(function () use ($card, $data, $actor) {
            $transaction = TollTransaction::create([
                ...$data,
                'toll_card_id' => $card->getKey(),
                // Kendaraan default mengikuti unit yang ditempeli kartu.
                'vehicle_id' => $data['vehicle_id'] ?? $card->vehicle_id,
                'created_by' => $actor->getKey(),
            ]);

            $this->recalculate($card);

            return $transaction;
        });
    }

    public function deleteTransaction(TollTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $card = $transaction->tollCard;
            $transaction->delete();
            $this->recalculate($card);
        });
    }

    /**
     * FR-M5-08 — rekonsiliasi saldo sistem vs saldo aktual kartu.
     *
     * @throws BusinessRuleException bila alasan penyesuaian kosong
     */
    public function reconcile(
        TollCard $card,
        float $actualBalance,
        string $reason,
        User $actor,
        ?Carbon $date = null,
    ): TollAdjustment {
        if (blank($reason)) {
            throw BusinessRuleException::rule(
                'FR-M5-08',
                'Penyesuaian saldo wajib menyertakan alasan.',
            );
        }

        return DB::transaction(function () use ($card, $actualBalance, $reason, $actor, $date) {
            $systemBalance = $this->recalculate($card);
            $difference = round($actualBalance - $systemBalance, 2);

            $adjustment = TollAdjustment::create([
                'toll_card_id' => $card->getKey(),
                'adjustment_date' => ($date ?? Carbon::today())->toDateString(),
                'system_balance' => $systemBalance,
                'actual_balance' => $actualBalance,
                'amount' => $difference,
                'reason' => $reason,
                'created_by' => $actor->getKey(),
            ]);

            $this->recalculate($card);

            return $adjustment;
        });
    }

    /**
     * FR-M5-07 — kartu aktif dengan saldo di bawah ambang minimum.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, TollCard>
     */
    public function cardsBelowMinimum()
    {
        return TollCard::query()
            ->with('vehicle')
            ->belowMinimum()
            ->orderBy('balance')
            ->get();
    }
}
