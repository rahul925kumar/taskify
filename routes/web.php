<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'adminLogin'])->name('admin.login.submit');
    Route::get('/employee/login', [LoginController::class, 'showEmployeeLoginForm'])->name('employee.login');
    Route::post('/employee/request-otp', [LoginController::class, 'employeeRequestOtp'])->name('employee.request-otp');
    Route::get('/employee/verify-otp', [LoginController::class, 'showVerifyOtpForm'])->name('employee.verify-otp.form');
    Route::post('/employee/verify-otp', [LoginController::class, 'verifyOtp'])->name('employee.verify-otp');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::resource('employees', EmployeeController::class);
    Route::resource('clients', ClientController::class)->except(['show']);

    Route::resource('tasks', AdminTaskController::class);
    Route::get('/tasks-kanban', [AdminTaskController::class, 'kanban'])->name('tasks.kanban');
    Route::patch('/tasks/{task}/status', [AdminTaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/comment', [AdminTaskController::class, 'addComment'])->name('tasks.comment');
    Route::post('/tasks/{task}/attachment', [AdminTaskController::class, 'addAttachment'])->name('tasks.attachment');
    Route::post('/tasks/{task}/reassign', [AdminTaskController::class, 'reassign'])->name('tasks.reassign');

    Route::get('/leaves', [AdminLeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/{leave}', [AdminLeaveController::class, 'show'])->name('leaves.show');
    Route::patch('/leaves/{leave}/approve', [AdminLeaveController::class, 'approve'])->name('leaves.approve');
    Route::patch('/leaves/{leave}/reject', [AdminLeaveController::class, 'reject'])->name('leaves.reject');
    Route::post('/leaves/{leave}/bulk-reassign', [AdminLeaveController::class, 'bulkReassign'])->name('leaves.bulk-reassign');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
});

// Employee Routes
Route::prefix('employee')->middleware(['auth', 'employee'])->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');

    Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/kanban', [EmployeeTaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('/tasks/{task}', [EmployeeTaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{task}/status', [EmployeeTaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/comment', [EmployeeTaskController::class, 'addComment'])->name('tasks.comment');
    Route::post('/tasks/{task}/attachment', [EmployeeTaskController::class, 'addAttachment'])->name('tasks.attachment');

    Route::get('/leaves', [EmployeeLeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [EmployeeLeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [EmployeeLeaveController::class, 'store'])->name('leaves.store');
});
