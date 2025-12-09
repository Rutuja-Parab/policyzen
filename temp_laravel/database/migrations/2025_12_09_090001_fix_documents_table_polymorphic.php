<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Add polymorphic columns if they don't exist
            if (!Schema::hasColumn('documents', 'documentable_type')) {
                $table->string('documentable_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('documents', 'documentable_id')) {
                $table->unsignedBigInteger('documentable_id')->nullable()->after('documentable_type');
            }
            
            // Add index for polymorphic relationship
            $table->index(['documentable_type', 'documentable_id'], 'documents_documentable_index');
            
            // Add timestamps if they don't exist
            if (!Schema::hasColumn('documents', 'created_at')) {
                $table->timestamps();
            }
        });

        // Migrate existing data from policy_id and endorsement_id to polymorphic columns (if columns exist)
        if (Schema::hasColumn('documents', 'policy_id')) {
            DB::statement("
                UPDATE documents 
                SET documentable_type = 'App\\\\Models\\\\InsurancePolicy', 
                    documentable_id = policy_id 
                WHERE policy_id IS NOT NULL AND documentable_id IS NULL
            ");
        }

        if (Schema::hasColumn('documents', 'endorsement_id')) {
            DB::statement("
                UPDATE documents 
                SET documentable_type = 'App\\\\Models\\\\PolicyEndorsement', 
                    documentable_id = endorsement_id 
                WHERE endorsement_id IS NOT NULL AND documentable_id IS NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_documentable_index');
            if (Schema::hasColumn('documents', 'documentable_type')) {
                $table->dropColumn('documentable_type');
            }
            if (Schema::hasColumn('documents', 'documentable_id')) {
                $table->dropColumn('documentable_id');
            }
        });
    }
};
