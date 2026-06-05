<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->string('vendor');
            $table->text('item_description');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'received'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['routine', 'repair', 'modification']);
            $table->text('description');
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('vendor')->nullable();
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_id')->constrained('users')->onDelete('cascade');
            $table->date('meeting_date');
            $table->text('notes')->nullable();
            $table->text('outcome')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('meeting_logs');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('purchase_orders');
    }
};
