<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'captain', 'kagawad', 'staff') NOT NULL DEFAULT 'staff'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'secretary', 'captain', 'kagawad', 'staff'])
                    ->default('staff')
                    ->change();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $captainCount = DB::table('users')->where('role', 'captain')->count();

            if ($captainCount > 0) {
                throw new RuntimeException('Cannot remove the captain role while captain users exist. Reassign them explicitly before rollback.');
            }

            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'kagawad', 'staff') NOT NULL DEFAULT 'staff'");
        } else {
            $captainCount = DB::table('users')->where('role', 'captain')->count();

            if ($captainCount > 0) {
                throw new RuntimeException('Cannot remove the captain role while captain users exist. Reassign them explicitly before rollback.');
            }

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'secretary', 'kagawad', 'staff'])
                    ->default('staff')
                    ->change();
            });
        }
    }
};
