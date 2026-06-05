<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->foreignId('opportunity_id')
                ->nullable()
                ->constrained('opportunities')
                ->nullOnDelete();
            $table->string('type')
                ->comment('meeting|call|visit|follow_up|email|demo');
            $table->string('subject');
            $table->text('notes')->nullable();
            $table->dateTime('activity_date');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('outcome')->nullable();
            $table->text('next_action')->nullable();
            $table->date('next_action_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
