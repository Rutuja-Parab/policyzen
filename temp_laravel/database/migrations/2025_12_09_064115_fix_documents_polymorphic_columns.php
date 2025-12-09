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
        Schema::table('documents', function (Blueprint $table) {
            // First drop the old foreign key columns if they exist
            if (Schema::hasColumn('documents', 'policy_id')) {
                $table->dropForeign(['policy_id']);
                $table->dropColumn('policy_id');
            }
            
            if (Schema::hasColumn('documents', 'endorsement_id')) {
                $table->dropForeign(['endorsement_id']);
                $table->dropColumn('endorsement_id');
            }

            // Add the new polymorphic columns
            $table->string('documentable_type')->after('document_type');
            $table->unsignedBigInteger('documentable_id')->after('documentable_type');
            $table->index(['documentable_type', 'documentable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['documentable_type', 'documentable_id']);
            $table->dropColumn(['documentable_type', 'documentable_id']);

            // Restore the old foreign key columns
            $table->foreignId('policy_id')->nullable()->constrained('policies')->nullOnDelete();
            $table->foreignId('endorsement_id')->nullable()->constrained('endorsements')->nullOnDelete();
        });
    }
};
