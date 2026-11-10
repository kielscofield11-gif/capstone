<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite refuses to drop a column while an index still references it,
        // so remove the column indexes before dropping the columns themselves.
        if (Schema::hasColumn('payments', 'return_token')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropUnique(['return_token']));
        }

        if (Schema::hasColumn('payments', 'provider_checkout_id')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropIndex(['provider_checkout_id']));
        }

        if (Schema::hasColumn('payments', 'provider_intent_id')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropIndex(['provider_intent_id']));
        }

        if (Schema::hasColumn('payments', 'provider_payment_id')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropIndex(['provider_payment_id']));
        }

        $columns = array_values(array_filter(
            ['provider_checkout_id', 'provider_intent_id', 'provider_payment_id', 'checkout_url', 'return_token'],
            fn (string $column) => Schema::hasColumn('payments', $column),
        ));

        if ($columns !== []) {
            Schema::table('payments', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider', 20)->default('cash')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('payments', 'provider_checkout_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('provider_checkout_id', 100)->nullable()->index()->after('provider');
            });
        }

        if (! Schema::hasColumn('payments', 'provider_intent_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('provider_intent_id', 100)->nullable()->index()->after('provider_checkout_id');
            });
        }

        if (! Schema::hasColumn('payments', 'provider_payment_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('provider_payment_id', 100)->nullable()->index()->after('provider_intent_id');
            });
        }

        if (! Schema::hasColumn('payments', 'checkout_url')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->text('checkout_url')->nullable()->after('payment_method');
            });
        }

        if (! Schema::hasColumn('payments', 'return_token')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('return_token', 64)->nullable()->unique()->after('checkout_url');
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider', 20)->default('paymongo')->change();
        });
    }
};
