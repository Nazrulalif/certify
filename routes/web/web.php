<?php

use App\Http\Controllers\Web\App\DashboardController;
use App\Http\Controllers\Web\App\GlobalSearchController;
use App\Http\Controllers\Web\App\TemplateController;
use App\Http\Controllers\Web\App\RegistrationController;
use App\Http\Controllers\Web\App\VerificationController;
use Illuminate\Support\Facades\Route;

// Include authentication routes
require __DIR__ . '/auth.php';
require __DIR__ . '/user.php';
require __DIR__ . '/template.php';
require __DIR__ . '/event.php';
require __DIR__ . '/certificate.php';

// Public verification routes
Route::get('/verify', [VerificationController::class, 'index'])->name('verify.index');
Route::post('/verify', [VerificationController::class, 'verify'])->name('verify.check');
Route::get('/verify/{certificateNumber}', [VerificationController::class, 'show'])->name('verify.certificate');

// Protected Routes (require authentication)
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search
    Route::get('/global-search', [GlobalSearchController::class, 'search'])->middleware(['auth'])->name('global.search');
    // Route::get('events/{event}/registrations', [RegistrationController::class, 'index'])
    //     ->name('events.registrations.index');
    // Template Management (Root and User can access)
    Route::middleware('user')->group(function () {
        Route::resource('templates', TemplateController::class);
        Route::post('templates/{template}/save-fields', [TemplateController::class, 'saveFields'])->name('templates.save-fields');
        Route::post('templates/{template}/set-default', [TemplateController::class, 'setDefault'])->name('templates.set-default');
    });
});
