<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_code')->unique();
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->string('title');
            $table->decimal('denomination', 15, 2);
            $table->decimal('purchase_price', 15, 2);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->string('status')->default('available')
                ->comment('available|used|expired|cancelled');
            $table->dateTime('used_at')->nullable();
            $table->foreignId('used_by_booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete();
            $table->foreignId('issued_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
