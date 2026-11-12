<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the payments table that belonged to the document fee payment
     * feature: payments are no longer recorded, so document fees are simply
     * collected at the hall and the release gate no longer requires a payment
     * record. Guarded with dropIfExists so fresh installs, which no longer
     * create this table, remain unaffected.
     */
    public function up(): void
    {
        DB::table('number_sequences')->where('sequence_key', 'document_payment')->delete();

        Schema::dropIfExists('payments');
    }

    public function down(): void
    {
        // No-op: the creating migrations have been removed. Restore payments
        // from git history if the feature is ever reintroduced.
    }
};
