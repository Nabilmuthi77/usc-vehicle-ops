<?php

use App\Enums\ServiceScheduleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-M4-04 & BR-06 — jadwal servis per unit kendaraan.
 *
 * `remaining_km` dan `status` dihitung ulang oleh ServiceScheduleService
 * setiap kali odometer berubah maupun oleh perintah `usc_vehicle_ops:check-service`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('interval_km')->nullable();
            $table->unsignedSmallInteger('interval_months')->nullable();
            $table->unsignedInteger('last_service_odometer')->nullable();
            $table->date('last_service_date')->nullable();
            $table->unsignedInteger('next_due_odometer')->nullable();
            $table->date('next_due_date')->nullable();
            $table->integer('remaining_km')->nullable();
            // FR-M4-05 — estimasi tanggal jatuh tempo dari rata-rata pemakaian.
            $table->date('estimated_due_date')->nullable();
            $table->enum('status', ServiceScheduleStatus::values())
                ->default(ServiceScheduleStatus::Aman->value);
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['vehicle_id', 'service_type_id']);
            $table->index(['status', 'remaining_km']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_service_schedules');
    }
};
