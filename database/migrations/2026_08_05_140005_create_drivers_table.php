<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FR-M1-04 — master driver dengan pengingat SIM kedaluwarsa. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('employee_id', 40)->nullable()->unique();
            $table->string('license_number', 40);
            $table->string('license_type', 40);
            $table->date('license_expiry');
            $table->string('phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'license_expiry']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
