<?php

use App\Http\Controllers\Web\App\TemplateController;
use App\Http\Controllers\Web\App\UsersController;
use Illuminate\Support\Facades\Route;
// Authenticated Routes (only accessible when logged in)

Route::middleware('auth')->group(function () {

    // Template Management (Root and User can access)
    Route::middleware('user')->group(function () {
        Route::resource('templates', TemplateController::class);
        Route::post('templates/{template}/save-fields', [TemplateController::class, 'saveFields'])->name('templates.save-fields');
        Route::post('templates/{template}/set-default', [TemplateController::class, 'setDefault'])->name('templates.set-default');
        Route::get('templates/{template}/preview', [TemplateController::class, 'downloadPreview'])->name('templates.preview');
    });
});

