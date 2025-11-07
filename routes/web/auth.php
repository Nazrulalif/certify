<?php

use App\Http\Controllers\Web\App\MyProfilController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Guest Routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registration Routes
    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// Authenticated Routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    // Logout Route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // My Profile Route
    Route::get('/my-profile', [MyProfilController::class, 'index'])->name('my-profile');
    Route::put('/my-profile/{id}', [MyProfilController::class, 'update'])->name('my-profile.update');
});
