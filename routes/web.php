<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CareerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\OkuController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WelfareController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'signup'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/statistics', [DashboardController::class, 'statistics'])
        ->middleware('throttle:30,1')
        ->name('dashboard.statistics');

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin,jkm_officer')->group(function () {
        Route::get('/profile', [AdminAccountController::class, 'profile'])->name('profile');
        Route::put('/profile', [AdminAccountController::class, 'updateProfile'])->name('profile.update');
        Route::get('/settings', [AdminAccountController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminAccountController::class, 'updateSettings'])->name('settings.update');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/peranan/{role}', [AdminUserController::class, 'roleIndex'])
            ->whereIn('role', ['super_admin', 'jkm_officer', 'employer', 'oku_user'])
            ->name('users.role');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/audit', [AdminUserController::class, 'audit'])->name('audit');
        Route::get('/audit/export', [AdminUserController::class, 'exportAudit'])->middleware('throttle:10,1')->name('audit.export');
    });

    Route::middleware('role:oku_user')->group(function () {
        Route::get('/profil-kerjaya', [CareerProfileController::class, 'show'])->name('career-profile.show');
        Route::put('/profil-kerjaya', [CareerProfileController::class, 'save'])->name('career-profile.save');
        Route::get('/profil-kerjaya/dokumen/{type}', [CareerProfileController::class, 'document'])->name('career-profile.document');
    });

    Route::middleware('role:super_admin,jkm_officer')->group(function () {
        Route::post('/oku/import', [OkuController::class, 'import'])->name('oku.import');
        Route::get('/oku/import/template', [OkuController::class, 'importTemplate'])->name('oku.import-template');
        Route::resource('oku', OkuController::class);
        Route::get('/oku/{oku}/find-jobs', [OkuController::class, 'findJobs'])->name('oku.find-jobs');
        Route::get('/oku/{oku}/dokumen/{type}', [CareerProfileController::class, 'staffDocument'])->name('oku.document');
        Route::put('/oku/{oku}/verification', [CareerProfileController::class, 'verify'])->name('oku.verify');
        Route::get('/employers/create', [EmployerController::class, 'create'])->name('employers.create');
        Route::get('/employers/{employer}/edit', [EmployerController::class, 'edit'])->name('employers.edit');
    });

    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index')
        ->middleware('role:super_admin,jkm_officer,employer,oku_user');
    Route::post('/jobs/{job}/interest', [JobController::class, 'expressInterest'])
        ->middleware('role:oku_user')
        ->name('jobs.interest');
    Route::middleware('role:super_admin,jkm_officer,employer')->group(function () {
        Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
        Route::apiResource('employers', EmployerController::class);
        Route::apiResource('jobs', JobController::class)->except('index');
    });

    Route::prefix('welfare')->name('welfare.')->middleware('role:super_admin,jkm_officer,oku_user')->group(function () {
        Route::get('/', [WelfareController::class, 'index'])->name('index');
        Route::get('/create', [WelfareController::class, 'create'])->name('create');
        Route::post('/', [WelfareController::class, 'store'])->name('store');
        Route::get('/{welfareApplication}', [WelfareController::class, 'show'])->name('show');
        Route::put('/{welfareApplication}/status', [WelfareController::class, 'updateStatus'])->name('update-status')->middleware('role:super_admin,jkm_officer');
        Route::post('/{welfareApplication}/schedule-review', [WelfareController::class, 'scheduleReview'])->name('schedule-review')->middleware('role:super_admin,jkm_officer');
    });

    Route::middleware('role:super_admin,jkm_officer')->group(function () {
        Route::get('/reports/employment', [ReportController::class, 'employmentStats'])->name('reports.employment');
        Route::get('/reports/employment/export', [ReportController::class, 'exportEmployment'])
            ->middleware('throttle:10,1')
            ->name('reports.employment-export');
        Route::get('/reports/welfare', [ReportController::class, 'welfareStats'])->name('reports.welfare');
        Route::get('/reports/welfare/export', [ReportController::class, 'exportWelfare'])
            ->middleware('throttle:10,1')
            ->name('reports.welfare-export');
        Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    });
});
