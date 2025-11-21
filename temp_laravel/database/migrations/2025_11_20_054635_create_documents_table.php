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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
        
            // Since policies.id is BIGINT auto-increment, use foreignId()
            $table->foreignId('policy_id')->nullable()->constrained('policies')->nullOnDelete();
        
            // If endorsements.id is also BIGINT, change this too:
            $table->foreignId('endorsement_id')->nullable()->constrained('endorsements')->nullOnDelete();
        
            // users.id is also BIGINT
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
        
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->enum('document_type', [
                'POLICY_DOCUMENT',
                'ENDORSEMENT_DOCUMENT',
                'FINANCIAL_DOCUMENT',
                'OTHER'
            ]);
        
            $table->timestamp('uploaded_at');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
