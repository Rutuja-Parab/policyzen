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
use App\Http\Controllers\StudentPolicyController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\NotificationController;

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
    Route::post('/policies/bulk-action', [PolicyController::class, 'bulkAction'])->name('policies.bulk-action');

    // Policy entity management
    Route::post('/policies/{policy}/add-entity', [PolicyController::class, 'addEntity'])->name('policies.add-entity');
    Route::post('/policies/{policy}/remove-entity', [PolicyController::class, 'removeEntity'])->name('policies.remove-entity');
    Route::post('/policies/{policy}/upload-documents', [PolicyController::class, 'uploadDocuments'])->name('policies.upload-documents');
    Route::put('/documents/{document}', [PolicyController::class, 'updateDocument'])->name('documents.update');
    Route::delete('/documents/{document}', [PolicyController::class, 'destroyDocument'])->name('documents.destroy');

    // Student Policy Management (Health Insurance)
    Route::prefix('policies/{policy}/students')->group(function () {
        Route::get('/', [StudentPolicyController::class, 'index'])->name('policies.students.index');
        Route::post('/add', [StudentPolicyController::class, 'addStudents'])->name('policies.students.add');
        Route::post('/remove', [StudentPolicyController::class, 'removeStudents'])->name('policies.students.remove');
        Route::get('/audit-logs', [StudentPolicyController::class, 'auditLogs'])->name('policies.students.audit-logs');
        Route::get('/endorsement/{endorsementId}/download', [StudentPolicyController::class, 'downloadEndorsement'])->name('policies.students.endorsement.download');
    });

    // Endorsements
    Route::get('/endorsements', [EndorsementController::class, 'index'])->name('endorsements.index');
    Route::get('/endorsements/create', [EndorsementController::class, 'create'])->name('endorsements.create');
    Route::post('/endorsements', [EndorsementController::class, 'store'])->name('endorsements.store');
    Route::get('/endorsements/{endorsement}', [EndorsementController::class, 'show'])->name('endorsements.show');
    Route::get('/endorsements/{endorsement}/edit', [EndorsementController::class, 'edit'])->name('endorsements.edit');
    Route::put('/endorsements/{endorsement}', [EndorsementController::class, 'update'])->name('endorsements.update');
    Route::delete('/endorsements/{endorsement}', [EndorsementController::class, 'destroy'])->name('endorsements.destroy');
    Route::post('/endorsements/{endorsement}/add-entities', [EndorsementController::class, 'addEntities'])->name('endorsements.add-entities');
    Route::post('/endorsements/{endorsement}/remove-entity', [EndorsementController::class, 'removeEntity'])->name('endorsements.remove-entity');
    Route::post('/endorsements/{endorsement}/upload-documents', [EndorsementController::class, 'uploadDocuments'])->name('endorsements.upload-documents');
    Route::post('/endorsements/bulk-action', [EndorsementController::class, 'bulkAction'])->name('endorsements.bulk-action');

    // API route for getting policy entities
    Route::get('/policies/{policy_id}/entities', [EndorsementController::class, 'getPolicyEntities'])->name('policies.entities');

    // Entities
    Route::prefix('entities')->group(function () {
        Route::get('/', [EntityController::class, 'index'])->name('entities.index');

        // Employees
        Route::get('/employees', [EntityController::class, 'employees'])->name('entities.employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'webCreate'])->name('entities.employees.create');
        Route::post('/employees', [EmployeeController::class, 'webStore'])->name('entities.employees.store');
        Route::get('/employees/{employee}', [EmployeeController::class, 'webShow'])->name('entities.employees.show');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'webEdit'])->name('entities.employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'webUpdate'])->name('entities.employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'webDestroy'])->name('entities.employees.destroy');
        Route::post('/employees/bulk-action', [EntityController::class, 'employeesBulkAction'])->name('entities.employees.bulk-action');

        // Students
        Route::get('/students', [EntityController::class, 'students'])->name('entities.students.index');
        Route::get('/students/create', [StudentController::class, 'webCreate'])->name('entities.students.create');
        Route::post('/students', [StudentController::class, 'webStore'])->name('entities.students.store');
        Route::get('/students/import', [StudentController::class, 'webImport'])->name('entities.students.import');
        Route::post('/students/import', [StudentController::class, 'webImportProcess'])->name('entities.students.import.process');
        Route::get('/students/{student}', [StudentController::class, 'webShow'])->name('entities.students.show');
        Route::get('/students/{student}/edit', [StudentController::class, 'webEdit'])->name('entities.students.edit');
        Route::put('/students/{student}', [StudentController::class, 'webUpdate'])->name('entities.students.update');
        Route::delete('/students/{student}', [StudentController::class, 'webDestroy'])->name('entities.students.destroy');
        Route::post('/students/bulk-action', [EntityController::class, 'studentsBulkAction'])->name('entities.students.bulk-action');

        // Vessels
        Route::get('/vessels', [EntityController::class, 'vessels'])->name('entities.vessels.index');
        Route::get('/vessels/create', [VesselController::class, 'webCreate'])->name('entities.vessels.create');
        Route::post('/vessels', [VesselController::class, 'webStore'])->name('entities.vessels.store');
        Route::get('/vessels/{vessel}', [VesselController::class, 'webShow'])->name('entities.vessels.show');
        Route::get('/vessels/{vessel}/edit', [VesselController::class, 'webEdit'])->name('entities.vessels.edit');
        Route::put('/vessels/{vessel}', [VesselController::class, 'webUpdate'])->name('entities.vessels.update');
        Route::delete('/vessels/{vessel}', [VesselController::class, 'webDestroy'])->name('entities.vessels.destroy');
        Route::post('/vessels/bulk-action', [EntityController::class, 'vesselsBulkAction'])->name('entities.vessels.bulk-action');

        // Vehicles
        Route::get('/vehicles', [EntityController::class, 'vehicles'])->name('entities.vehicles.index');
        Route::get('/vehicles/create', [VehicleController::class, 'webCreate'])->name('entities.vehicles.create');
        Route::post('/vehicles', [VehicleController::class, 'webStore'])->name('entities.vehicles.store');
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'webShow'])->name('entities.vehicles.show');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'webEdit'])->name('entities.vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'webUpdate'])->name('entities.vehicles.update');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'webDestroy'])->name('entities.vehicles.destroy');
        Route::post('/vehicles/bulk-action', [EntityController::class, 'vehiclesBulkAction'])->name('entities.vehicles.bulk-action');

        // Courses
        Route::get('/courses', [CourseController::class, 'webIndex'])->name('entities.courses.index');
        Route::get('/courses/create', [CourseController::class, 'webCreate'])->name('entities.courses.create');
        Route::post('/courses', [CourseController::class, 'webStore'])->name('entities.courses.store');
        Route::get('/courses/{course}', [CourseController::class, 'webShow'])->name('entities.courses.show');
        Route::get('/courses/{course}/edit', [CourseController::class, 'webEdit'])->name('entities.courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'webUpdate'])->name('entities.courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'webDestroy'])->name('entities.courses.destroy');
    });

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/chart/{type}', [ReportsController::class, 'getChartData'])->name('reports.chart');
    Route::get('/reports/export/{format}', [ReportsController::class, 'export'])->name('reports.export');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::get('/audit-logs/statistics', [AuditLogController::class, 'statistics'])->name('audit-logs.statistics');
    Route::post('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/{notification}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/delete-all-read', [NotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');
    
    // Web-based notification API endpoints for the notification bell
    Route::get('/notifications/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});
