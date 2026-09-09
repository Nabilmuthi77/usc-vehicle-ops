<?php

use App\Enums\FuelClaimStatus;
use App\Enums\FuelType;
use App\Enums\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M3-01 s.d. FR-M3-09 — transaksi pengisian BBM sekaligus klaim reimbursement. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('claimant_id')->constrained('users')->restrictOnDelete();

            $table->timestamp('transaction_datetime');
            $table->string('station_name');
            $table->foreignId('station_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->enum('fuel_type', FuelType::values());
            $table->decimal('liters', 8, 2);
            $table->decimal('price_per_liter', 12, 2);
            $table->decimal('total_cost', 14, 2);
            // FR-M3-09 — nominal setelah koreksi Admin GA; jadi dasar perhitungan biaya.
            $table->decimal('approved_amount', 14, 2)->nullable();
            $table->enum('payment_method', PaymentMethod::values())->default(PaymentMethod::Tunai->value);
            $table->unsignedInteger('odometer');
            $table->boolean('is_full_tank')->default(true);
            $table->string('receipt_number', 60);
            $table->string('receipt_photo_path')->nullable();

            // --- BR-05 hasil perhitungan konsumsi full-to-full ---
            $table->unsignedInteger('km_since_last_fill')->nullable();
            $table->decimal('consumption_km_per_liter', 8, 2)->nullable();

            // --- FR-M3-05 deteksi anomali ---
            $table->boolean('is_anomaly')->default(false);
            $table->json('anomaly_reason')->nullable();

            // --- FR-M3-06 alur klaim ---
            $table->enum('status', FuelClaimStatus::values())->default(FuelClaimStatus::Draft->value);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('correction_note')->nullable();
            // FR-M3-14 — klaim melewati batas waktu perlu persetujuan khusus.
            $table->boolean('is_late_claim')->default(false);
            $table->text('late_claim_approval_note')->nullable();

            $table->foreignId('reimbursement_batch_id')->nullable()
                ->constrained('reimbursement_batches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'transaction_datetime']);
            $table->index(['claimant_id', 'status']);
            $table->index(['status', 'transaction_datetime']);
            $table->index('reimbursement_batch_id');

            // FR-M3-08 & BR-15 — satu nota hanya boleh diklaim satu kali.
            $table->unique(['station_name', 'receipt_number', 'transaction_datetime'], 'fuel_receipt_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_transactions');
    }
};
