<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->decimal('target_mobil_short', 15, 2)->default(0)->after('target_revenue');
            $table->decimal('target_bis_short', 15, 2)->default(0)->after('target_mobil_short');
            $table->decimal('target_evoucher', 15, 2)->default(0)->after('target_bis_short');
            $table->decimal('target_mobil_long', 15, 2)->default(0)->after('target_evoucher');
            $table->decimal('target_bis_long', 15, 2)->default(0)->after('target_mobil_long');
            $table->decimal('target_supir', 15, 2)->default(0)->after('target_bis_long');
        });
    }

    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropColumn([
                'target_mobil_short',
                'target_bis_short',
                'target_evoucher',
                'target_mobil_long',
                'target_bis_long',
                'target_supir',
            ]);
        });
    }
};
