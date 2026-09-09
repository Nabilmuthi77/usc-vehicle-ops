<?php

use App\Enums\ReimbursementBatchStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M3-10 s.d. FR-M3-12 — batch reimbursement klaim BBM per periode per pengaju. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reimbursement_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 40)->unique();
            $table->foreignId('claimant_id')->constrained('users')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->unsignedInteger('item_count')->default(0);
            $table->enum('status', ReimbursementBatchStatus::values())
                ->default(ReimbursementBatchStatus::Disusun->value);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->text('payment_note')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['claimant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimbursement_batches');
    }
};
