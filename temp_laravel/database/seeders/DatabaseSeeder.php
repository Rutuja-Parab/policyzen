<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed companies
        $company = DB::table('companies')->insertGetId([
            'name' => 'Tech Solutions Inc',
            'parent_company_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed users
        $user = DB::table('users')->insertGetId([
            'company_id' => $company,
            'name' => 'Admin User',
            'email' => 'admin@policyzen.com',
            'role' => 'ADMIN',
            'password_hash' => bcrypt('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed employees
        $employee1 = DB::table('employees')->insertGetId([
            'company_id' => $company,
            'employee_code' => 'EMP002',
            'name' => 'Employee 2',
            'status' => 'ACTIVE',
            'department' => 'HR',
            'position' => 'Junior HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employee2 = DB::table('employees')->insertGetId([
            'company_id' => $company,
            'employee_code' => 'EMP001',
            'name' => 'Employee 1',
            'status' => 'ACTIVE',
            'department' => 'HR',
            'position' => 'Senior HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed students
        $student = DB::table('students')->insertGetId([
            'company_id' => $company,
            'student_id' => 'STU001',
            'name' => 'Student 1',
            'status' => 'ACTIVE',
            'course' => 'CS',
            'year_of_study' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed vessels
        $vessel = DB::table('vessels')->insertGetId([
            'company_id' => $company,
            'vessel_name' => 'Vessel 1',
            'imo_number' => '123456',
            'status' => 'ACTIVE',
            'vessel_type' => 'Cargo Ship',
            'flag' => 'IND',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed vehicles
        $vehicle = DB::table('vehicles')->insertGetId([
            'company_id' => $company,
            'registration_number' => 'ABC123',
            'make' => 'Maruti',
            'model' => '1234',
            'year' => 2025,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed entities
        $entity = DB::table('entities')->insertGetId([
            'company_id' => $company,
            'type' => 'EMPLOYEE',
            'entity_id' => $employee1,
            'description' => 'Employee: Employee 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed policies
        $policy1 = DB::table('policies')->insertGetId([
            'entity_id' => $entity,
            'policy_number' => 'POL002',
            'insurance_type' => 'ACCIDENT',
            'provider' => 'SafeGuard Insurance',
            'start_date' => '2024-06-01',
            'end_date' => '2025-05-31',
            'sum_insured' => 75000.00,
            'premium_amount' => 800.00,
            'status' => 'CANCELLED',
            'created_by' => $user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $policy2 = DB::table('policies')->insertGetId([
            'entity_id' => $entity,
            'policy_number' => 'POL001',
            'insurance_type' => 'HEALTH',
            'provider' => 'HealthCare Plus',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'sum_insured' => 50000.00,
            'premium_amount' => 1200.00,
            'status' => 'CANCELLED',
            'created_by' => $user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed endorsements
        DB::table('endorsements')->insert([
            'policy_id' => $policy2,
            'endorsement_number' => 'END001',
            'description' => 'Increased coverage limit from $50,000 to $75,000 due to salary adjustment',
            'effective_date' => '2024-07-01',
            'created_by' => $user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
