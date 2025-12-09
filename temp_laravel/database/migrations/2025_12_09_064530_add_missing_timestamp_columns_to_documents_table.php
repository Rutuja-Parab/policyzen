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
            // Add missing timestamp columns if they don't exist
            if (!Schema::hasColumn('documents', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('uploaded_at');
            }
            
            if (!Schema::hasColumn('documents', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};