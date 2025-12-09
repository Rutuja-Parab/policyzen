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
        Schema::create('endorsement_entities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('endorsement_id');
            $table->unsignedBigInteger('entity_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('endorsement_id')->references('id')->on('endorsements')->onDelete('cascade');
            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');

            // Unique constraint to prevent duplicate endorsement-entity relationships
            $table->unique(['endorsement_id', 'entity_id']);

            // Indexes for performance
            $table->index('endorsement_id');
            $table->index('entity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endorsement_entities');
    }
};

