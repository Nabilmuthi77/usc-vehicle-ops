<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M4-08 — rincian item pekerjaan & sparepart per realisasi servis. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_record_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->enum('type', ['jasa', 'sparepart'])->default('sparepart');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();

            $table->index('service_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_record_items');
    }
};
