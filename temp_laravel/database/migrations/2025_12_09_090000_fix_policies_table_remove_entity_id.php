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
        Schema::table('policies', function (Blueprint $table) {
            // Check if entity_id column exists before trying to drop it
            if (Schema::hasColumn('policies', 'entity_id')) {
                // Drop the foreign key constraint first
                $table->dropForeign(['entity_id']);
                // Then drop the column
                $table->dropColumn('entity_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            // Re-add the entity_id column if needed (nullable this time)
            if (!Schema::hasColumn('policies', 'entity_id')) {
                $table->foreignId('entity_id')->nullable()->constrained('entities');
            }
        });
    }
};
