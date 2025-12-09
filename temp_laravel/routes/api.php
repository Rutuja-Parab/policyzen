<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VesselController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\InsurancePolicyController;
use App\Http\Controllers\PolicyEndorsementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('/', function () {
    return response()->json(['message' => 'Insurance Policy Management System API', 'version' => '1.0.0']);
});

Route::get('/health', function () {
    return response()->json(['status' => 'healthy', 'timestamp' => now()->toISOString()]);
});

// Companies
Route::apiResource('companies', CompanyController::class)->names([
    'index' => 'api.companies.index',
    'store' => 'api.companies.store',
    'show' => 'api.companies.show',
    'update' => 'api.companies.update',
    'destroy' => 'api.companies.destroy',
]);

// Employees
Route::apiResource('employees', EmployeeController::class)->names([
    'index' => 'api.employees.index',
    'store' => 'api.employees.store',
    'show' => 'api.employees.show',
    'update' => 'api.employees.update',
    'destroy' => 'api.employees.destroy',
]);

// Students
Route::apiResource('students', StudentController::class)->names([
    'index' => 'api.students.index',
    'store' => 'api.students.store',
    'show' => 'api.students.show',
    'update' => 'api.students.update',
    'destroy' => 'api.students.destroy',
]);

// Vessels
Route::apiResource('vessels', VesselController::class)->names([
    'index' => 'api.vessels.index',
    'store' => 'api.vessels.store',
    'show' => 'api.vessels.show',
    'update' => 'api.vessels.update',
    'destroy' => 'api.vessels.destroy',
]);

// Vehicles
Route::apiResource('vehicles', VehicleController::class)->names([
    'index' => 'api.vehicles.index',
    'store' => 'api.vehicles.store',
    'show' => 'api.vehicles.show',
    'update' => 'api.vehicles.update',
    'destroy' => 'api.vehicles.destroy',
]);

// Courses
Route::apiResource('courses', CourseController::class)->names([
    'index' => 'api.courses.index',
    'store' => 'api.courses.store',
    'show' => 'api.courses.show',
    'update' => 'api.courses.update',
    'destroy' => 'api.courses.destroy',
]);

// Entities
Route::get('entities', [EntityController::class, 'index'])->name('api.entities.index');

// Policies
Route::apiResource('policies', InsurancePolicyController::class)->names([
    'index' => 'api.policies.index',
    'store' => 'api.policies.store',
    'show' => 'api.policies.show',
    'update' => 'api.policies.update',
    'destroy' => 'api.policies.destroy',
]);
Route::put('policies/{policy}/status', [InsurancePolicyController::class, 'updateStatus'])->name('api.policies.status');
Route::get('policies/expiring', [InsurancePolicyController::class, 'expiring'])->name('api.policies.expiring');

// Endorsements
Route::apiResource('endorsements', PolicyEndorsementController::class)->names([
    'index' => 'api.endorsements.index',
    'store' => 'api.endorsements.store',
    'show' => 'api.endorsements.show',
    'update' => 'api.endorsements.update',
    'destroy' => 'api.endorsements.destroy',
]);

// Dashboard
Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

// Search
Route::get('search', [SearchController::class, 'search'])->name('api.search');