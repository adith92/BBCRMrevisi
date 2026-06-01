<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->enum('brand', ['bigbird','goldenbird','cititrans','executive']);
            $table->string('model');
            $table->integer('capacity');
            $table->integer('year');
            $table->enum('status', ['available','on_trip','maintenance','inactive'])->default('available');
            $table->foreignId('pool_id')->nullable()->constrained();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
