<?php

use App\Enums\VehicleClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M5-05 — master ruas & tarif tol agar tarif terisi otomatis. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toll_rates', function (Blueprint $table) {
            $table->id();
            $table->string('road_section');
            $table->string('entry_gate');
            $table->string('exit_gate');
            $table->enum('vehicle_class', VehicleClass::values())->default(VehicleClass::I->value);
            $table->decimal('amount', 14, 2);
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['entry_gate', 'exit_gate', 'vehicle_class', 'effective_date'], 'toll_rate_unique');
            $table->index(['road_section', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toll_rates');
    }
};
