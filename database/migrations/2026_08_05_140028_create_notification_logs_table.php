<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M7-05 — riwayat pengiriman notifikasi beserta status berhasil/gagal. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_key', 60);
            $table->string('channel', 20);
            $table->string('notification_class');
            $table->string('recipient')->nullable();
            $table->string('subject')->nullable();
            $table->enum('status', ['terkirim', 'gagal'])->default('terkirim');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['event_key', 'status']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
