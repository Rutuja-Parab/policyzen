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
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->date('dob')->nullable()->after('phone');
            $table->integer('age')->nullable()->after('dob');
            $table->string('gender')->nullable()->after('age');
            $table->string('rank')->nullable()->after('gender');
            $table->string('batch')->nullable()->after('year_of_study');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone', 'dob', 'age', 'gender', 'rank', 'batch']);
        });
    }
};

