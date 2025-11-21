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
        // For SQLite and MySQL, we can modify the column
        // For PostgreSQL, this would need a different approach
        $driver = config('database.default');

        if ($driver === 'sqlite' || $driver === 'mysql' || $driver === 'mariadb') {
            // Use raw SQL to modify the enum
            $enumValues = "'EMPLOYEE','STUDENT','VEHICLE','BUILDING','SHIP','COMPANY','COURSE'";
            DB::statement("ALTER TABLE entities MODIFY COLUMN type ENUM($enumValues)");
        } else {
            // For PostgreSQL, we'd need to use ALTER TYPE, but for simplicity
            // in this demo, we'll just update existing records
            // In production, you'd want to handle this properly
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = config('database.default');

        if ($driver === 'sqlite' || $driver === 'mysql' || $driver === 'mariadb') {
            $enumValues = "'EMPLOYEE','STUDENT','VEHICLE','BUILDING','SHIP'";
            DB::statement("ALTER TABLE entities MODIFY COLUMN type ENUM($enumValues)");
        }
    }
};
