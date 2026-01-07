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
            $table->string('batch')->nullable()->after('course');
            $table->date('date_of_joining')->nullable()->after('batch');
            $table->date('date_of_exiting')->nullable()->after('date_of_joining');
            $table->decimal('sum_insured', 12, 2)->nullable()->after('date_of_exiting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['batch', 'date_of_joining', 'date_of_exiting', 'sum_insured']);
        });
    }
};
