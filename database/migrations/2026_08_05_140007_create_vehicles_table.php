<?php

use App\Enums\FuelType;
use App\Enums\Ownership;
use App\Enums\VehicleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M1-02 & FR-M1-11 — master kendaraan beserta penanda paritas pelat. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number', 20)->unique();
            $table->string('brand', 60);
            $table->string('model', 60);
            $table->unsignedSmallInteger('year');
            $table->string('color', 40)->nullable();
            $table->string('chassis_number', 60)->nullable();
            $table->string('engine_number', 60)->nullable();
            $table->enum('fuel_type', FuelType::values());
            $table->decimal('tank_capacity', 8, 2)->nullable();
            $table->string('transmission', 20)->nullable();
            $table->enum('ownership', Ownership::values())->default(Ownership::Milik->value);
            $table->foreignId('rental_contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            // FR-M1-11 — digit terakhir pelat, di-maintain otomatis oleh model Vehicle.
            $table->unsignedTinyInteger('plate_last_digit')->nullable();

            $table->unsignedInteger('initial_odometer')->default(0);
            $table->unsignedInteger('current_odometer')->default(0);
            $table->enum('status', VehicleStatus::values())->default(VehicleStatus::Tersedia->value);
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'ownership']);
            $table->index('plate_last_digit');
            $table->index('department_id');
        });

        Schema::table('toll_cards', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // SQLite tidak mendukung DROP FOREIGN KEY; constraint ikut terhapus bersama tabel.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('toll_cards', function (Blueprint $table) {
                $table->dropForeign(['vehicle_id']);
            });
        }

        Schema::dropIfExists('vehicles');
    }
};
