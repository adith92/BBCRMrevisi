<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')
                ->constrained('opportunities')
                ->cascadeOnDelete();
            $table->foreignId('requested_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('current_approver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('type')->default('discount')
                ->comment('discount|pricing|exception');
            $table->decimal('discount_percent', 5, 2);
            $table->decimal('original_price', 15, 2);
            $table->decimal('final_price', 15, 2);
            $table->unsignedTinyInteger('level')->default(1)
                ->comment('1=manager, 2=gm, 3=director');
            $table->string('status')->default('pending')
                ->comment('pending|approved|rejected|escalated');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
