<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M5-02 — top-up saldo kartu e-toll. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toll_topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toll_card_id')->constrained()->restrictOnDelete();
            $table->date('topup_date');
            $table->decimal('amount', 14, 2);
            $table->string('method', 40)->nullable();
            $table->string('reference_number', 60)->nullable();
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['toll_card_id', 'topup_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toll_topups');
    }
};
