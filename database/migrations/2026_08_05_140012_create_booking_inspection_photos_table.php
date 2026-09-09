<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M2-20 — foto kondisi minimal 4 sisi saat serah terima. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_inspection_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('booking_inspections')->cascadeOnDelete();
            $table->string('photo_path');
            $table->enum('position', ['depan', 'belakang', 'kanan', 'kiri', 'interior', 'odometer']);
            $table->timestamps();

            $table->index('inspection_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_inspection_photos');
    }
};
