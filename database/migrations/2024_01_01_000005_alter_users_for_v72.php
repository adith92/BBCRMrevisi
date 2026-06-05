<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('manager_id')->nullable()->after('id');
            $table->string('sales_level')->nullable()->after('role')
                ->comment('junior|senior|key_account');

            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // SQLite does not support MODIFY COLUMN for enum changes.
        // We use a CHECK constraint approach via DB::statement for SQLite,
        // or ALTER COLUMN for MySQL/PostgreSQL.
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: recreate the check by dropping and re-adding using raw DDL.
            // Since SQLite does not support DROP CONSTRAINT, we rely on the
            // application layer validation. The column already exists as a
            // varchar/text in SQLite (enums are stored as strings), so we
            // simply leave the existing column – new role values (director,
            // manager) are now valid at the application level.
            // Nothing to do for SQLite here.
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('director','gm','manager','sales','operational','finance') NOT NULL DEFAULT 'sales'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['manager_id', 'sales_level']);
        });

        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('gm','sales','operational','finance') NOT NULL DEFAULT 'sales'");
        }
    }
};
