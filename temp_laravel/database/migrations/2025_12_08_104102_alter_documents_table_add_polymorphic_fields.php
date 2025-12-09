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
            $table->dropForeign(['policy_id']);
            $table->dropForeign(['endorsement_id']);
            $table->dropColumn(['policy_id', 'endorsement_id']);

            $table->string('documentable_type');
            $table->string('documentable_id');
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

            $table->foreignId('policy_id')->nullable()->constrained('policies')->nullOnDelete();
            $table->foreignId('endorsement_id')->nullable()->constrained('endorsements')->nullOnDelete();
        });
    }
};