<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            // Note: the [household_id, is_household_head] composite lives in
            // 2026_08_21_000002 as residents_household_head_index. Do not add
            // a second composite here to avoid duplicate indexes.
            $table->index('is_senior', 'residents_is_senior_index');
            $table->index('is_pwd', 'residents_is_pwd_index');
            $table->index('is_voter', 'residents_is_voter_index');
        });

        Schema::table('blotters', function (Blueprint $table) {
            $table->index(['status', 'incident_date'], 'blotters_status_incident_date_index');
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'document_requests_status_created_index');
        });
    }

    public function down(): void
    {
        // The household_id single-column index belongs to the FK created in
        // 2024_01_01_000002 and is never dropped by up(), so down() must not
        // re-create it (that caused Duplicate key name failures).
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropIndex('document_requests_status_created_index');
        });

        Schema::table('blotters', function (Blueprint $table) {
            $table->dropIndex('blotters_status_incident_date_index');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropIndex('residents_is_voter_index');
            $table->dropIndex('residents_is_pwd_index');
            $table->dropIndex('residents_is_senior_index');
        });
    }
};
