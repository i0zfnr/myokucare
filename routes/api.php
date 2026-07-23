<?php

use App\Http\Controllers\Api\OkuApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/oku')->name('api.oku.')->group(function () {
    Route::get('/data', [OkuApiController::class, 'getOkuData'])->name('data');
    Route::get('/matching-jobs', [OkuApiController::class, 'getMatchingJobs'])->name('matching-jobs');
    Route::get('/job-recommendations', [OkuApiController::class, 'getJobRecommendations'])->name('job-recommendations');
    Route::get('/employment-stats', [OkuApiController::class, 'getEmploymentStats'])->name('employment-stats');
    Route::post('/submit-interest', [OkuApiController::class, 'submitJobInterest'])->name('submit-interest');
});
