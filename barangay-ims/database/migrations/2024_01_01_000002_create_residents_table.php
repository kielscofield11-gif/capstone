<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('birth_date');
            $table->string('birthplace')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('civil_status', ['single', 'married', 'widowed', 'separated'])->default('single');
            $table->string('occupation')->nullable();
            $table->string('nationality')->default('Filipino');
            $table->string('blood_type')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('purok')->nullable();
            $table->string('street_address')->nullable();
            $table->boolean('is_voter')->default(false);
            $table->boolean('is_pwd')->default(false);
            $table->boolean('is_senior')->default(false);
            $table->boolean('is_4ps')->default(false);
            $table->foreignId('household_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_household_head')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
