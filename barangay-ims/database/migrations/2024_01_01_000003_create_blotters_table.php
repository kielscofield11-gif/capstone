<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blotters', function (Blueprint $table) {
            $table->id();
            $table->string('blotter_number')->unique();
            $table->foreignId('complainant_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->foreignId('respondent_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->string('incident_type');
            $table->date('incident_date');
            $table->string('incident_location')->nullable();
            $table->text('details');
            $table->enum('status', ['pending', 'hearing', 'resolved', 'dismissed'])->default('pending');
            $table->date('hearing_date')->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blotters');
    }
};
