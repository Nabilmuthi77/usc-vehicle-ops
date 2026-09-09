<?php

use App\Enums\FuelType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M3-19 — master harga BBM per jenis dengan riwayat perubahan. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->enum('fuel_type', FuelType::values());
            $table->decimal('price_per_liter', 12, 2);
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['fuel_type', 'effective_date']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
