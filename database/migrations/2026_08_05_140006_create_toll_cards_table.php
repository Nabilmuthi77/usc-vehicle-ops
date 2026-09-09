<?php

use App\Enums\TollCardStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-M5-01 — master kartu e-toll.
 *
 * Relasi kartu ke kendaraan disimpan hanya di sisi ini (`vehicle_id` unik)
 * agar tidak ada dua kolom yang harus disinkronkan; asumsi PRD §15
 * "satu kendaraan menggunakan satu kartu e-toll utama" tetap terpenuhi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toll_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_number', 40)->unique();
            $table->string('issuer', 60);
            // FK ke vehicles ditambahkan pada migrasi vehicles (dibuat setelah tabel ini).
            $table->unsignedBigInteger('vehicle_id')->nullable()->unique();
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('min_balance_alert', 14, 2)->default(100000);
            $table->enum('status', TollCardStatus::values())->default(TollCardStatus::Aktif->value);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toll_cards');
    }
};
