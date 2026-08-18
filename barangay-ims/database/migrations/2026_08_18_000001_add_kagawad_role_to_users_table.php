<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'captain', 'kagawad', 'staff') NOT NULL DEFAULT 'staff'");
            DB::table('users')->where('role', 'captain')->update(['role' => 'kagawad']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'kagawad', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }
};
