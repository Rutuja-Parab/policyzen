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
        Schema::create('policy_entities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('policy_id');
            $table->unsignedBigInteger('entity_id');
            $table->date('effective_date')->nullable(); // When entity was added to policy
            $table->date('termination_date')->nullable(); // When entity was removed from policy
            $table->string('status')->default('ACTIVE'); // ACTIVE, TERMINATED
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('policy_id')->references('id')->on('policies')->onDelete('cascade');
            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');

            // Unique constraint to prevent duplicate policy-entity relationships
            $table->unique(['policy_id', 'entity_id']);

            // Indexes for performance
            $table->index(['policy_id', 'status']);
            $table->index(['entity_id', 'status']);
            $table->index('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_entities');
    }
};
