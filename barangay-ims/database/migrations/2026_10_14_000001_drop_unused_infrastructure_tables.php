<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove infrastructure tables that belong to unused features: Sanctum API
     * tokens (no API routes exist) and database-backed sessions (the app uses
     * the file driver locally and the array driver in tests). Guarded with
     * dropIfExists so fresh installs, which no longer create these tables,
     * remain unaffected.
     */
    public function up(): void
    {
        foreach (['personal_access_tokens', 'sessions'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // No-op: the creating migrations have been removed. Restore Sanctum or
        // database sessions via their framework migrations if ever needed.
    }
};
