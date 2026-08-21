<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->text('requirements')->nullable()->after('description');
            $table->unsignedSmallInteger('processing_days')->nullable()->after('requirements');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('email');
            $table->index(['birth_date', 'last_name', 'first_name'], 'residents_duplicate_lookup_index');
            $table->index(['household_id', 'is_household_head'], 'residents_household_head_index');
        });

        Schema::table('blotters', function (Blueprint $table) {
            $table->index(['status', 'hearing_date'], 'blotters_hearing_reminder_index');
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'document_requests_pending_index');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'audit_logs_user_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_user_date_index');
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropIndex('document_requests_pending_index');
        });

        Schema::table('blotters', function (Blueprint $table) {
            $table->dropIndex('blotters_hearing_reminder_index');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropIndex('residents_duplicate_lookup_index');
            $table->dropIndex('residents_household_head_index');
            $table->dropColumn('photo_path');
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn(['requirements', 'processing_days']);
        });
    }
};
