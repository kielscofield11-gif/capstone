<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->string('control_number')->unique();
            $table->foreignId('resident_id')->constrained();
            $table->foreignId('document_type_id')->constrained();
            $table->text('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'released', 'cancelled'])->default('pending');
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approved_date')->nullable();
            $table->date('released_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
