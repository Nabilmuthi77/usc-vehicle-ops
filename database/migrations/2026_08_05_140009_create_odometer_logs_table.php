<?php

use App\Enums\OdometerSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-M4-03 & BR-03 — jejak seluruh pembaruan odometer kendaraan.
 *
 * `is_correction` menandai input mundur yang diizinkan Admin disertai alasan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odometer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('odometer');
            $table->integer('delta_km')->default(0);
            $table->timestamp('recorded_at');
            $table->enum('source', OdometerSource::values());
            $table->nullableMorphs('sourceable');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_correction')->default(false);
            $table->text('correction_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'recorded_at']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odometer_logs');
    }
};
