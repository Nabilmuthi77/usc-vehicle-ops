<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M5-06 — jejak import mutasi transaksi tol dari CSV/Excel mobile banking. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toll_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toll_card_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path')->nullable();
            // Pemetaan kolom file sumber -> kolom sistem.
            $table->json('column_mapping')->nullable();
            $table->unsignedInteger('row_total')->default(0);
            $table->unsignedInteger('row_imported')->default(0);
            $table->unsignedInteger('row_skipped')->default(0);
            $table->json('errors')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toll_import_batches');
    }
};
