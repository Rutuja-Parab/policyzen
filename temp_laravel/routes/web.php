<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VesselController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EndorsementController;
use App\Http\Controllers\EntityController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Companies
    Route::get('/companies', [CompanyController::class, 'webIndex'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'webCreate'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'webStore'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'webShow'])->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'webEdit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'webUpdate'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'webDestroy'])->name('companies.destroy');

    // Policies
    Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
    Route::get('/policies/create', [PolicyController::class, 'create'])->name('policies.create');
    Route::post('/policies', [PolicyController::class, 'store'])->name('policies.store');
    Route::get('/policies/{policy}', [PolicyController::class, 'show'])->name('policies.show');
    Route::get('/policies/{policy}/edit', [PolicyController::class, 'edit'])->name('policies.edit');
    Route::put('/policies/{policy}', [PolicyController::class, 'update'])->name('policies.update');
    Route::delete('/policies/{policy}', [PolicyController::class, 'destroy'])->name('policies.destroy');

    // Policy entity management
    Route::post('/policies/{policy}/add-entity', [PolicyController::class, 'addEntity'])->name('policies.add-entity');
    Route::post('/policies/{policy}/remove-entity', [PolicyController::class, 'removeEntity'])->name('policies.remove-entity');

    // Endorsements
    Route::get('/endorsements', [EndorsementController::class, 'index'])->name('endorsements.index');
    Route::get('/endorsements/create', [EndorsementController::class, 'create'])->name('endorsements.create');
    Route::post('/endorsements', [EndorsementController::class, 'store'])->name('endorsements.store');
    Route::get('/endorsements/{endorsement}', [EndorsementController::class, 'show'])->name('endorsements.show');
    Route::get('/endorsements/{endorsement}/edit', [EndorsementController::class, 'edit'])->name('endorsements.edit');
    Route::put('/endorsements/{endorsement}', [EndorsementController::class, 'update'])->name('endorsements.update');
    Route::delete('/endorsements/{endorsement}', [EndorsementController::class, 'destroy'])->name('endorsements.destroy');

    // Entities
    Route::prefix('entities')->group(function () {
        Route::get('/', [EntityController::class, 'index'])->name('entities.index');

        // Employees
        Route::get('/employees', [EmployeeController::class, 'webIndex'])->name('entities.employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'webCreate'])->name('entities.employees.create');
        Route::post('/employees', [EmployeeController::class, 'webStore'])->name('entities.employees.store');
        Route::get('/employees/{employee}', [EmployeeController::class, 'webShow'])->name('entities.employees.show');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'webEdit'])->name('entities.employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'webUpdate'])->name('entities.employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'webDestroy'])->name('entities.employees.destroy');

        // Students
        Route::get('/students', [StudentController::class, 'webIndex'])->name('entities.students.index');
        Route::get('/students/create', [StudentController::class, 'webCreate'])->name('entities.students.create');
        Route::post('/students', [StudentController::class, 'webStore'])->name('entities.students.store');
        Route::get('/students/{student}', [StudentController::class, 'webShow'])->name('entities.students.show');
        Route::get('/students/{student}/edit', [StudentController::class, 'webEdit'])->name('entities.students.edit');
        Route::put('/students/{student}', [StudentController::class, 'webUpdate'])->name('entities.students.update');
        Route::delete('/students/{student}', [StudentController::class, 'webDestroy'])->name('entities.students.destroy');

        // Vessels
        Route::get('/vessels', [VesselController::class, 'webIndex'])->name('entities.vessels.index');
        Route::get('/vessels/create', [VesselController::class, 'webCreate'])->name('entities.vessels.create');
        Route::post('/vessels', [VesselController::class, 'webStore'])->name('entities.vessels.store');
        Route::get('/vessels/{vessel}', [VesselController::class, 'webShow'])->name('entities.vessels.show');
        Route::get('/vessels/{vessel}/edit', [VesselController::class, 'webEdit'])->name('entities.vessels.edit');
        Route::put('/vessels/{vessel}', [VesselController::class, 'webUpdate'])->name('entities.vessels.update');
        Route::delete('/vessels/{vessel}', [VesselController::class, 'webDestroy'])->name('entities.vessels.destroy');

        // Vehicles
        Route::get('/vehicles', [VehicleController::class, 'webIndex'])->name('entities.vehicles.index');
        Route::get('/vehicles/create', [VehicleController::class, 'webCreate'])->name('entities.vehicles.create');
        Route::post('/vehicles', [VehicleController::class, 'webStore'])->name('entities.vehicles.store');
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'webShow'])->name('entities.vehicles.show');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'webEdit'])->name('entities.vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'webUpdate'])->name('entities.vehicles.update');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'webDestroy'])->name('entities.vehicles.destroy');

        // Courses
        Route::get('/courses', [CourseController::class, 'webIndex'])->name('entities.courses.index');
        Route::get('/courses/create', [CourseController::class, 'webCreate'])->name('entities.courses.create');
        Route::post('/courses', [CourseController::class, 'webStore'])->name('entities.courses.store');
        Route::get('/courses/{course}', [CourseController::class, 'webShow'])->name('entities.courses.show');
        Route::get('/courses/{course}/edit', [CourseController::class, 'webEdit'])->name('entities.courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'webUpdate'])->name('entities.courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'webDestroy'])->name('entities.courses.destroy');
    });

    // Other pages
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');


    Route::get('/search', function () {
        return view('search.index');
    })->name('search.index');
});
