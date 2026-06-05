<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('sub_number')->unique();
            $table->foreignId('opportunity_id')
                ->nullable()
                ->constrained('opportunities')
                ->nullOnDelete();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();
            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles')
                ->nullOnDelete();
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_rate', 15, 2);
            $table->string('billing_cycle')->default('monthly')
                ->comment('monthly|quarterly|yearly');
            $table->string('status')->default('active')
                ->comment('active|paused|terminated|expired');
            $table->date('last_billed_at')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Now add the FK from opportunities.subscription_id to subscriptions
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Drop FK on opportunities first
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
        });

        Schema::dropIfExists('subscriptions');
    }
};
