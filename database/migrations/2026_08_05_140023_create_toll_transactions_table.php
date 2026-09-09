<?php

use App\Enums\TollTransactionSource;
use App\Enums\VehicleClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M5-03 & FR-M5-04 — transaksi tol, dapat dibebankan ke peminjaman. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toll_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toll_card_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('transaction_datetime');
            $table->string('entry_gate')->nullable();
            $table->string('exit_gate')->nullable();
            $table->string('road_section')->nullable();
            $table->enum('vehicle_class', VehicleClass::values())->default(VehicleClass::I->value);
            $table->decimal('amount', 14, 2);
            $table->enum('source', TollTransactionSource::values())
                ->default(TollTransactionSource::Manual->value);
            $table->foreignId('import_batch_id')->nullable()
                ->constrained('toll_import_batches')->nullOnDelete();
            $table->string('reference_number', 60)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'transaction_datetime']);
            $table->index('toll_card_id');
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toll_transactions');
    }
};
