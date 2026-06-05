<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->dateTime('pickup_datetime');
            $table->dateTime('dropoff_datetime');
            $table->string('destination');
            $table->string('vehicle_type');
            $table->decimal('price', 15, 2);
            $table->enum('status', ['pending', 'confirmed', 'on_trip', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue'])->default('draft');
            $table->date('due_date');
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['transfer', 'cash', 'giro'])->default('transfer');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('bookings');
    }
};
