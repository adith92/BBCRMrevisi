<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('tier')->default('bronze')->after('status')
                ->comment('platinum|gold|silver|bronze');
            $table->date('first_contact_date')->nullable()->after('tier');
            $table->string('company_size')->nullable()->after('first_contact_date')
                ->comment('enterprise|mid|sme');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['tier', 'first_contact_date', 'company_size']);
        });
    }
};
