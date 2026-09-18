<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Ensure the full role set exists without demoting anyone.
            // A previous revision demoted captain -> kagawad here; that is now
            // handled explicitly by 2026_08_20_000001 which adds captain back.
            // Keep this migration non-destructive and idempotent.
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'captain', 'kagawad', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Non-destructive: keep the full set so rollback never orphans
            // captain users. Explicit removal lives in 2026_08_20's down().
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'captain', 'kagawad', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }
};
