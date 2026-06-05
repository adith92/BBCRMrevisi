<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->integer('capacity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('pic_name');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->string('industry')->nullable();
            $table->enum('status', ['active', 'prospect', 'inactive'])->default('active');
            $table->foreignId('assigned_sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('license_number')->unique();
            $table->enum('status', ['available', 'on_duty', 'off'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->enum('brand', ['bigbird', 'goldenbird', 'cititrans', 'executive']);
            $table->string('model');
            $table->integer('capacity');
            $table->year('year');
            $table->enum('status', ['available', 'on_trip', 'maintenance', 'inactive'])->default('available');
            $table->foreignId('pool_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('pools');
    }
};
