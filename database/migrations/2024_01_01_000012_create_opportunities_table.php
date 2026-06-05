<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('opp_number')->unique();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();
            $table->foreignId('sales_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->string('title');
            $table->string('stage')->default('prospecting')
                ->comment('prospecting|proposal|negotiation|won|lost');
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->decimal('final_value', 15, 2)->nullable();
            $table->unsignedInteger('pax')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('discount_approved')->default(false);
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete();
            // subscription_id added via a separate migration after subscriptions table exists
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
