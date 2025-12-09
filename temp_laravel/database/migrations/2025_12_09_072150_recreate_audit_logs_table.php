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
        // Drop the existing audit_logs table
        Schema::dropIfExists('audit_logs');
        
        // Recreate with bigint columns
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->unsignedBigInteger('policy_id')->nullable();
            $table->unsignedBigInteger('endorsement_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('transaction_type')->nullable();
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('performed_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the table
        Schema::dropIfExists('audit_logs');
        
        // Recreate with original structure (this would need to be manually restored)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('entity_type');
            $table->uuid('entity_id')->nullable();
            $table->uuid('policy_id')->nullable();
            $table->uuid('endorsement_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('transaction_type')->nullable();
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('performed_by');
            $table->timestamps();
        });
    }
};
