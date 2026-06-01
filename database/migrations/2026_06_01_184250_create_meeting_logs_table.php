<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('sales_id')->constrained('users');
            $table->dateTime('meeting_date');
            $table->text('notes')->nullable();
            $table->text('outcome')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->enum('status', ['pending','done'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_logs');
    }
};
