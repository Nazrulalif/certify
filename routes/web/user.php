<?php

use App\Http\Controllers\Web\App\UsersController;
use Illuminate\Support\Facades\Route;

// Authenticated Routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/create', [UsersController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [UsersController::class, 'edit'])->name('edit');

        Route::post('/', [UsersController::class, 'store'])->name('store');
        Route::put('/{id}/update', [UsersController::class, 'update'])->name('update');
        Route::delete('/{id}/destroy', [UsersController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [UsersController::class, 'bulk_destroy'])->name('destroy-bulk');
        Route::put('/{id}/deactive', [UsersController::class, 'deactive'])->name('deactive');
        Route::put('/{id}/reactive', [UsersController::class, 'reactive'])->name('reactive');
    });
});
