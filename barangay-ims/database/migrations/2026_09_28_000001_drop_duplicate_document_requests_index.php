<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the duplicate composite index created by an earlier revision
        // of 2026_08_21_000002. Fresh installs no longer create it.
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();
            $exists = DB::selectOne(
                'select count(*) as aggregate from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ?',
                [$database, 'document_requests', 'document_requests_pending_index']
            );

            if ($exists && (int) $exists->aggregate > 0) {
                Schema::table('document_requests', function ($table) {
                    $table->dropIndex('document_requests_pending_index');
                });
            }
        } elseif ($driver === 'sqlite') {
            $indexes = DB::select("select name from sqlite_master where type = 'index' and tbl_name = 'document_requests' and name = 'document_requests_pending_index'");
            if (count($indexes) > 0) {
                Schema::table('document_requests', function ($table) {
                    $table->dropIndex('document_requests_pending_index');
                });
            }
        }
    }

    public function down(): void
    {
        // No-op: dropping a duplicate index is not reversible.
    }
};
