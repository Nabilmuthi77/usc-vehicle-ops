<?php

use App\Enums\InspectionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M2-20 & FR-M2-21 — pemeriksaan saat serah terima dan pengembalian. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->enum('type', InspectionType::values());
            // Kelengkapan: ban_serep, dongkrak, p3k, stnk, apar.
            $table->json('checklist')->nullable();
            $table->unsignedInteger('odometer')->nullable();
            $table->decimal('fuel_level', 4, 2)->nullable();
            $table->text('condition_notes')->nullable();
            $table->boolean('damage_found')->default(false);
            $table->text('damage_notes')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inspected_at');
            $table->timestamps();

            $table->unique(['booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_inspections');
    }
};
