<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->year('period_year');
            $table->unsignedTinyInteger('period_month');

            // Targets
            $table->unsignedInteger('target_meetings')->default(0);
            $table->unsignedInteger('target_calls')->default(0);
            $table->unsignedInteger('target_visits')->default(0);
            $table->unsignedInteger('target_opportunities')->default(0);
            $table->unsignedInteger('target_won')->default(0);
            $table->decimal('target_revenue', 15, 2)->default(0);

            // Actuals (auto-updated by Observers)
            $table->unsignedInteger('actual_meetings')->default(0);
            $table->unsignedInteger('actual_calls')->default(0);
            $table->unsignedInteger('actual_visits')->default(0);
            $table->unsignedInteger('actual_opportunities')->default(0);
            $table->unsignedInteger('actual_won')->default(0);
            $table->decimal('actual_revenue', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'period_year', 'period_month'], 'sales_targets_user_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
