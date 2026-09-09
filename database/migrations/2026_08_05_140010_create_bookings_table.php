<?php

use App\Enums\BookingStatus;
use App\Enums\DurationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-M2-01 s.d. FR-M2-27 — peminjaman kendaraan.
 *
 * `vehicle_id` sengaja nullable di level database (pemohon tidak memilih unit,
 * BR-19) dan divalidasi wajib pada Form Request saat transisi ke `disetujui`
 * (BR-20).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 40)->unique();

            // --- Diisi pemohon saat pengajuan (5 field wajib, FR-M2-01) ---
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('booking_date');
            $table->string('destination');
            $table->text('purpose');
            $table->boolean('odd_even_zone')->default(false);
            $table->enum('duration_type', DurationType::values());
            $table->text('additional_note')->nullable();
            $table->boolean('is_urgent')->default(false);

            // --- Diisi Admin GA saat approval (FR-M2-09, FR-M2-10) ---
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('self_drive')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('assignment_note')->nullable();

            // --- Diisi saat serah terima & pengembalian (FR-M2-20 s.d. FR-M2-23) ---
            $table->timestamp('actual_start_datetime')->nullable();
            $table->timestamp('actual_end_datetime')->nullable();
            $table->unsignedInteger('odometer_start')->nullable();
            $table->unsignedInteger('odometer_end')->nullable();
            $table->unsignedInteger('distance_traveled')->nullable();
            // Level BBM dicatat sebagai fraksi tangki 0.00 - 1.00.
            $table->decimal('fuel_level_start', 4, 2)->nullable();
            $table->decimal('fuel_level_end', 4, 2)->nullable();

            // --- FR-M2-24 keterlambatan pengembalian ---
            $table->boolean('is_overdue')->default(false);
            $table->timestamp('overdue_notified_at')->nullable();

            // --- BR-12 koreksi peminjaman selesai ---
            $table->text('correction_reason')->nullable();

            $table->enum('status', BookingStatus::values())->default(BookingStatus::MenungguApproval->value);
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'booking_date']);
            $table->index(['driver_id', 'booking_date']);
            $table->index(['status', 'booking_date']);
            $table->index(['requester_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
