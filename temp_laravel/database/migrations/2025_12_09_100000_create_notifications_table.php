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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // POLICY_EXPIRY_WARNING, ENDORSEMENT_EXPIRY_WARNING, etc.
            $table->string('title');
            $table->text('message');
            $table->string('priority')->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->json('data')->nullable(); // Additional data (policy_id, endorsement_id, etc.)
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // When notification should be automatically removed
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};