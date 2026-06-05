<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')
                ->constrained('product_categories')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('base_price', 15, 2);
            $table->string('unit')->default('trip')
                ->comment('pax|unit|trip');
            $table->unsignedInteger('min_pax')->default(1);
            $table->unsignedInteger('max_pax')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
