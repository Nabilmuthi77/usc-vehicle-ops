<?php

use App\Enums\CostBorneBy;
use App\Enums\ServiceCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M4-08 s.d. FR-M4-11 — realisasi servis berkala maupun insidental. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('category', ServiceCategory::values())->default(ServiceCategory::Berkala->value);
            $table->date('service_date');
            $table->unsignedInteger('odometer');
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number', 60)->nullable();
            // FR-M4-19 — biaya opsional (kosong untuk kendaraan sewa).
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->enum('cost_borne_by', CostBorneBy::values())->default(CostBorneBy::Perusahaan->value);
            // BR-16 — perubahan penanggung biaya wajib beralasan.
            $table->text('cost_borne_by_reason')->nullable();
            $table->unsignedInteger('next_due_odometer')->nullable();
            $table->string('damage_category')->nullable();
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'service_date']);
            $table->index(['category', 'cost_borne_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
