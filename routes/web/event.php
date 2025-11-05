<?php

use App\Http\Controllers\Web\App\EventController;
use App\Http\Controllers\Web\App\RegistrationController;
use Illuminate\Support\Facades\Route;

// Event Management Routes (Auth required)
Route::middleware(['auth'])->group(function () {
    Route::resource('events', EventController::class);
    Route::post('events/{event}/toggle-registration', [EventController::class, 'toggleRegistration'])
        ->name('events.toggle-registration');

    // New event configuration routes
    Route::get('templates/{template}/static-value-fields', [EventController::class, 'getStaticValueFields'])
        ->name('templates.static-value-fields');
    Route::get('templates/{template}/registration-form-preview', [EventController::class, 'getTemplateFormPreview'])
        ->name('templates.registration-form-preview');
    Route::get('events/{event}/registration-form-preview', [EventController::class, 'getRegistrationFormPreview'])
        ->name('events.registration-form-preview');
    Route::get('events/{event}/configuration-summary', [EventController::class, 'getConfigurationSummary'])
        ->name('events.configuration-summary');
    Route::post('events/{event}/static-values', [EventController::class, 'saveStaticValues'])
        ->name('events.save-static-values');

    // Registration management for events
    Route::get('events/{event}/registrations', [RegistrationController::class, 'index'])
        ->name('events.registrations.index');
    Route::patch('events/{event}/registrations/{registration}/status', [RegistrationController::class, 'updateStatus'])
        ->name('events.registrations.update-status');
    Route::delete('events/{event}/registrations/{registration}', [RegistrationController::class, 'destroy'])
        ->name('events.registrations.destroy');
    Route::post('events/{event}/registrations/bulk-destroy', [RegistrationController::class, 'bulkDestroy'])
        ->name('events.registrations.bulk-destroy');
});

// Public Registration Routes (No auth required)
Route::get('register/{slug}', [RegistrationController::class, 'show'])->name('register.show');
Route::post('register/{slug}', [RegistrationController::class, 'store'])->name('register.store');
Route::get('register-success', [RegistrationController::class, 'success'])->name('register.success');
