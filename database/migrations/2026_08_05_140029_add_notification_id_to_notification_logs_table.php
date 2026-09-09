<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-M7-05 — kaitkan log pengiriman ke UUID notifikasi.
 *
 * Selain memudahkan penelusuran ke notifikasi in-app terkait, kombinasi
 * (notification_id, channel) dipakai sebagai kunci idempoten agar satu
 * pengiriman hanya menghasilkan satu baris log.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->uuid('notification_id')->nullable()->after('id');
            $table->unique(['notification_id', 'channel'], 'notification_logs_delivery_unique');
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropUnique('notification_logs_delivery_unique');
            $table->dropColumn('notification_id');
        });
    }
};
