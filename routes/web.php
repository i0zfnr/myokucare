<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthRecoveryController;
use App\Http\Controllers\CareerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeletedRecordController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\EmploymentRelationshipController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FeatureControlController;
use App\Http\Controllers\GuidelineController;
use App\Http\Controllers\IdentityVerificationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobCandidateController;
use App\Http\Controllers\LanguageSettingsController;
use App\Http\Controllers\ManualReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\OkuController;
use App\Http\Controllers\QuarterlyProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WelfareController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::get('/firebase-messaging-sw.js', function () {
    return response()->view('firebase-messaging-sw', [
        'enabled' => (bool) config('services.firebase.enabled'),
        'firebaseConfig' => config('services.firebase.web'),
    ])->header('Content-Type', 'application/javascript; charset=UTF-8')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Service-Worker-Allowed', '/firebase-cloud-messaging-push-scope');
})->name('push.service-worker');
Route::get('/guideline', [GuidelineController::class, 'show'])->name('guideline.show');
Route::post('/guideline/activity', [GuidelineController::class, 'track'])->middleware('throttle:30,1')->name('guideline.track');
Route::post('/guideline/language', [GuidelineController::class, 'language'])->middleware('throttle:20,1')->name('guideline.language');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'signup'])->name('register.store');
    Route::get('/lupa-kata-laluan', [AuthRecoveryController::class, 'forgot'])->name('password.request');
    Route::post('/lupa-kata-laluan', [AuthRecoveryController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/tetap-semula-kata-laluan/{token}', [AuthRecoveryController::class, 'reset'])->name('password.reset');
    Route::post('/tetap-semula-kata-laluan', [AuthRecoveryController::class, 'updatePassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/sahkan-e-mel', [AuthRecoveryController::class, 'verificationNotice'])->name('verification.notice');
    Route::post('/sahkan-e-mel/hantar', [AuthRecoveryController::class, 'sendVerification'])->middleware('throttle:3,1')->name('verification.send');
    Route::get('/sahkan-e-mel/{id}/{hash}', [AuthRecoveryController::class, 'verifyEmail'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/push/config', [PushSubscriptionController::class, 'config'])->name('push.config');
    Route::post('/push/subscriptions', [PushSubscriptionController::class, 'store'])->middleware('throttle:10,1')->name('push.subscriptions.store');
    Route::delete('/push/subscriptions', [PushSubscriptionController::class, 'destroy'])->middleware('throttle:10,1')->name('push.subscriptions.destroy');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifikasi/{notification}', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/settings/language', [LanguageSettingsController::class, 'edit'])->name('language-settings.edit');
    Route::put('/settings/language', [LanguageSettingsController::class, 'update'])->name('language-settings.update');
    Route::prefix('verification')->name('identity-verification.')->middleware(['role:oku_user', 'identity.feature', 'throttle:identity-verification'])->group(function () {
        Route::get('/mykad', [IdentityVerificationController::class, 'show'])->name('show');
        Route::post('/session', [IdentityVerificationController::class, 'createSession'])->name('session.create');
        Route::post('/{session}/upload', [IdentityVerificationController::class, 'upload'])->name('upload');
        Route::get('/{session}/document/{document}', [IdentityVerificationController::class, 'document'])->name('document');
        Route::post('/{session}/process', [IdentityVerificationController::class, 'process'])->name('process');
        Route::post('/{session}/verify', [IdentityVerificationController::class, 'verify'])->name('verify');
        Route::get('/status', [IdentityVerificationController::class, 'status'])->name('status');
        Route::get('/{session}', [IdentityVerificationController::class, 'status'])->name('session.show');
        Route::post('/{session}/resubmit', [IdentityVerificationController::class, 'resubmit'])->name('resubmit');
        Route::post('/{session}/manual-review', [IdentityVerificationController::class, 'manualReview'])->name('manual-review');
    });
    Route::middleware('role:oku_user')->group(function () {
        Route::get('/pengesahan-profil', [QuarterlyProfileController::class, 'show'])->name('quarterly-profile.show');
        Route::put('/pengesahan-profil', [QuarterlyProfileController::class, 'update'])->name('quarterly-profile.update');
    });
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

    Route::prefix('identity-reviews')->name('identity-reviews.')->middleware('role:super_admin,jkm_officer')->group(function () {
        Route::get('/', [ManualReviewController::class, 'index'])->name('index');
        Route::get('/{manualReview}', [ManualReviewController::class, 'show'])->name('show');
        Route::get('/{manualReview}/document/{document}', [ManualReviewController::class, 'document'])->name('document');
        Route::put('/{manualReview}', [ManualReviewController::class, 'update'])->name('update');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/feature-controls', [FeatureControlController::class, 'index'])->name('feature-controls.index');
        Route::put('/feature-controls', [FeatureControlController::class, 'update'])->name('feature-controls.update');
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

    Route::prefix('deleted-records')->name('deleted-records.')->middleware('role:super_admin,jkm_officer')->group(function () {
        Route::get('/', [DeletedRecordController::class, 'index'])->name('index');
        Route::post('/employers/{employer}/restore', [DeletedRecordController::class, 'restoreEmployer'])->name('employers.restore');
        Route::post('/okus/{oku}/restore', [DeletedRecordController::class, 'restoreOku'])->name('okus.restore');
        Route::delete('/employers/{employer}/permanent', [DeletedRecordController::class, 'permanentEmployer'])->name('employers.permanent');
        Route::delete('/okus/{oku}/permanent', [DeletedRecordController::class, 'permanentOku'])->name('okus.permanent');
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
    Route::get('/employers', [EmployerController::class, 'index'])
        ->middleware('role:super_admin,jkm_officer,employer,oku_user')
        ->name('employers.index');
    Route::get('/employers/{employer}', [EmployerController::class, 'show'])
        ->middleware('role:super_admin,jkm_officer,employer,oku_user')
        ->name('employers.show');
    Route::resource('employments', EmploymentRelationshipController::class)
        ->middleware('role:super_admin,jkm_officer,employer,oku_user');
    Route::prefix('exports')->name('exports.')->middleware('role:super_admin,jkm_officer,employer,oku_user')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::post('/', [ExportController::class, 'store'])->middleware('throttle:10,1')->name('store');
        Route::get('/{export}', [ExportController::class, 'status'])->name('status');
        Route::get('/{export}/download', [ExportController::class, 'download'])->middleware('signed')->name('download');
        Route::delete('/{export}', [ExportController::class, 'destroy'])->name('destroy');
    });
    Route::middleware('role:super_admin,jkm_officer,employer')->group(function () {
        Route::get('/jobs/{job}/candidates', [JobCandidateController::class, 'index'])->name('jobs.candidates.index');
        Route::patch('/jobs/{job}/candidates/{jobInterest}', [JobCandidateController::class, 'update'])->name('jobs.candidates.update');
        Route::get('/jobs/{job}/candidates/{jobInterest}/resume', [JobCandidateController::class, 'resume'])->name('jobs.candidates.resume');
        Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
        Route::apiResource('employers', EmployerController::class)->except(['index', 'show']);
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
