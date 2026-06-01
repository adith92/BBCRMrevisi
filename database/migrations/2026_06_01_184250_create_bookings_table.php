<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('sales_id')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('driver_id')->constrained();
            $table->dateTime('pickup_datetime');
            $table->dateTime('dropoff_datetime');
            $table->string('destination');
            $table->string('vehicle_type');
            $table->decimal('price', 15, 2);
            $table->enum('status', ['pending','confirmed','on_trip','completed','cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
