<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number', 32)->unique();
            $table->string('provider', 20)->default('paymongo');
            $table->string('provider_checkout_id', 100)->nullable()->index();
            $table->string('provider_payment_id', 100)->nullable()->index();
            $table->string('provider_intent_id', 100)->nullable()->index();
            $table->string('status', 20)->default('pending');
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('PHP');
            $table->string('payment_method', 30)->nullable();
            $table->text('checkout_url')->nullable();
            $table->string('return_token', 64)->unique();
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['document_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
