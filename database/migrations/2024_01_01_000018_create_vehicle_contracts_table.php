<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnDelete();
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('contract_type')->default('pool')
                ->comment('dedicated|shared|pool');
            $table->decimal('rate', 15, 2)->nullable();
            $table->string('status')->default('active')
                ->comment('active|completed|cancelled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_contracts');
    }
};
