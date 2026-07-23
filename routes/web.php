<?php

use App\Http\Controllers\AuthController;
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

    Route::middleware('role:super_admin,jkm_officer')->group(function () {
        Route::resource('oku', OkuController::class);
        Route::get('/oku/{oku}/find-jobs', [OkuController::class, 'findJobs'])->name('oku.find-jobs');
    });

    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index')
        ->middleware('role:super_admin,jkm_officer,employer,oku_user,family_member');
    Route::middleware('role:super_admin,jkm_officer,employer')->group(function () {
        Route::apiResource('employers', EmployerController::class);
        Route::apiResource('jobs', JobController::class)->except('index');
    });

    Route::prefix('welfare')->name('welfare.')->middleware('role:super_admin,jkm_officer,oku_user,family_member')->group(function () {
        Route::get('/', [WelfareController::class, 'index'])->name('index');
        Route::get('/create', [WelfareController::class, 'create'])->name('create');
        Route::post('/', [WelfareController::class, 'store'])->name('store');
        Route::get('/{welfareApplication}', [WelfareController::class, 'show'])->name('show');
        Route::put('/{welfareApplication}/status', [WelfareController::class, 'updateStatus'])->name('update-status')->middleware('role:super_admin,jkm_officer');
        Route::post('/{welfareApplication}/schedule-review', [WelfareController::class, 'scheduleReview'])->name('schedule-review')->middleware('role:super_admin,jkm_officer');
    });

    Route::middleware('role:super_admin,jkm_officer,viewer')->group(function () {
        Route::get('/reports/employment', [ReportController::class, 'employmentStats'])->name('reports.employment');
        Route::get('/reports/welfare', [ReportController::class, 'welfareStats'])->name('reports.welfare');
        Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    });
});
