<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M7-04 — preferensi kanal notifikasi per pengguna per jenis kejadian. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Kunci kejadian, mis. booking_submitted, service_due, toll_balance_low.
            $table->string('event_key', 60);
            $table->boolean('via_database')->default(true);
            $table->boolean('via_mail')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
