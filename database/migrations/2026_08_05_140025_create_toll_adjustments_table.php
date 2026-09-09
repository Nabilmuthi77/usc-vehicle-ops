<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-M5-08 & BR-08 — rekonsiliasi saldo sistem vs saldo aktual kartu.
 *
 * `amount` bernilai positif atau negatif sesuai arah penyesuaian sehingga
 * rumus saldo tetap `Σ top-up − Σ transaksi ± Σ penyesuaian`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toll_card_id')->constrained()->cascadeOnDelete();
            $table->date('adjustment_date');
            $table->decimal('system_balance', 14, 2);
            $table->decimal('actual_balance', 14, 2);
            $table->decimal('amount', 14, 2);
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['toll_card_id', 'adjustment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toll_adjustments');
    }
};
