<?php

use App\Enums\VehicleDocumentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M1-03 — dokumen kendaraan (STNK, pajak, KIR, asuransi) + pengingat H-30. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', VehicleDocumentType::values());
            $table->string('document_number', 60)->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date');
            $table->string('file_path')->nullable();
            $table->unsignedSmallInteger('reminder_days')->default(30);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'document_type']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_documents');
    }
};
