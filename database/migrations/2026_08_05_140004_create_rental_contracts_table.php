<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M4-22 — master kontrak sewa dengan pengingat berakhir H-60. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_cost', 14, 2)->default(0);
            // Cakupan layanan: servis, ban, asuransi, pengganti_unit.
            $table->json('coverage')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone', 30)->nullable();
            $table->string('pic_email')->nullable();
            $table->string('document_path')->nullable();
            $table->unsignedSmallInteger('reminder_days')->default(60);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_contracts');
    }
};
