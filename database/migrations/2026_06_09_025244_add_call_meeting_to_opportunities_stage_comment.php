<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('stage')->default('call_meeting')
                ->comment('call_meeting|prospecting|proposal|negotiation|won|lost')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('stage')->default('prospecting')
                ->comment('prospecting|proposal|negotiation|won|lost')->change();
        });
    }
};
