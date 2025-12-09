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
        Schema::table('students', function (Blueprint $table) {
            // Drop columns that are no longer needed
            if (Schema::hasColumn('students', 'year_of_study')) {
                $table->dropColumn('year_of_study');
            }
            if (Schema::hasColumn('students', 'batch')) {
                $table->dropColumn('batch');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('year_of_study')->nullable();
            $table->string('batch')->nullable();
        });
    }
};
