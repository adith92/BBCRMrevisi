<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pools', function (Blueprint $table) {
            $table->string('region')->nullable()->after('capacity')
                ->comment('jakarta_utara|jakarta_selatan|jakarta_timur|jakarta_barat|jakarta_pusat|daerah');
        });
    }

    public function down(): void
    {
        Schema::table('pools', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};
