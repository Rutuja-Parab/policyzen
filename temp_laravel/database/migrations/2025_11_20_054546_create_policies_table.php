<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities');
            $table->string('policy_number')->unique();
            $table->enum('insurance_type', ['HEALTH', 'ACCIDENT', 'PROPERTY', 'VEHICLE', 'MARINE']);
            $table->string('provider');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('sum_insured', 15, 2);
            $table->decimal('premium_amount', 15, 2);
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'UNDER_REVIEW', 'CANCELLED'])->default('ACTIVE');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
