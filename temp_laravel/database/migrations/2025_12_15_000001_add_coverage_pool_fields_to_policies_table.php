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
            // Remove sum_insured and premium_amount columns
            $table->dropColumn('sum_insured');
            $table->dropColumn('premium_amount');

            // Add coverage pool columns
            $table->decimal('starting_coverage_pool', 15, 2)->after('end_date')->comment('Initial coverage pool amount');
            $table->decimal('available_coverage_pool', 15, 2)->after('starting_coverage_pool')->comment('Available coverage pool remaining');
            $table->decimal('utilized_coverage_pool', 15, 2)->after('available_coverage_pool')->comment('Utilized coverage pool amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            // Remove coverage pool columns
            $table->dropColumn('starting_coverage_pool');
            $table->dropColumn('available_coverage_pool');
            $table->dropColumn('utilized_coverage_pool');

            // Restore sum_insured and premium_amount columns
            $table->decimal('sum_insured', 15, 2)->after('end_date');
            $table->decimal('premium_amount', 15, 2)->after('sum_insured');
        });
    }
};
