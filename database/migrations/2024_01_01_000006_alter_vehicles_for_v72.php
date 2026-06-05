<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('color')->nullable()->after('capacity');
            $table->string('transmission')->nullable()->after('color')
                ->comment('manual|automatic');
            $table->date('stnk_expiry')->nullable()->after('transmission');
            $table->date('pajak_expiry')->nullable()->after('stnk_expiry');
            $table->string('bbm_type')->nullable()->after('pajak_expiry')
                ->comment('solar|pertamax|pertalite');
            $table->unsignedInteger('current_km')->default(0)->after('bbm_type');
            $table->year('year_manufactured')->nullable()->after('current_km');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'color',
                'transmission',
                'stnk_expiry',
                'pajak_expiry',
                'bbm_type',
                'current_km',
                'year_manufactured',
            ]);
        });
    }
};
