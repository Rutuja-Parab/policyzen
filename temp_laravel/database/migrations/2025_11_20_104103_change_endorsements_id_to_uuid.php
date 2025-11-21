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
        Schema::table('endorsements', function (Blueprint $table) {
            // Drop the auto-incrementing id column
            $table->dropColumn('id');

            // Add UUID id column
            $table->uuid('id')->primary()->first();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('endorsements', function (Blueprint $table) {
            // Drop UUID id column
            $table->dropColumn('id');

            // Add back auto-incrementing id column
            $table->id();
        });
    }
};
