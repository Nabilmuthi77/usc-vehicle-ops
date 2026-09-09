<?php

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M4-15 s.d. FR-M4-20 — permintaan servis ke vendor (terutama kendaraan sewa). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 40)->unique();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('current_odometer');
            $table->unsignedInteger('due_odometer')->nullable();
            $table->text('complaint_note')->nullable();
            $table->enum('status', ServiceRequestStatus::values())
                ->default(ServiceRequestStatus::Dibuat->value);
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_to_email')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            // FR-M4-18 — SLA vendor & downtime kendaraan.
            $table->unsignedSmallInteger('response_days')->nullable();
            $table->unsignedSmallInteger('downtime_days')->nullable();
            $table->text('vendor_response_note')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sent_at']);
            $table->index(['vehicle_id', 'status']);
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
