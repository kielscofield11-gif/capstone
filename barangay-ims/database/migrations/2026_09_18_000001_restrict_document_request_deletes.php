<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            // Drop cascade FKs created in 2024_01_01_000005 and re-add as
            // restrict so deleting a resident/type cannot wipe history.
            $table->dropForeign(['resident_id']);
            $table->dropForeign(['document_type_id']);
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->foreign('resident_id')->references('id')->on('residents')->restrictOnDelete();
            $table->foreign('document_type_id')->references('id')->on('document_types')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropForeign(['resident_id']);
            $table->dropForeign(['document_type_id']);
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->foreign('resident_id')->references('id')->on('residents')->cascadeOnDelete();
            $table->foreign('document_type_id')->references('id')->on('document_types')->cascadeOnDelete();
        });
    }
};
