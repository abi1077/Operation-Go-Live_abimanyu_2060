<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;

// Halaman Beranda / Katalog Proyek
Route::get('/', [ProjectController::class, 'index'])->name('home');

// Auth routes (guest)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout (auth)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Detail Proyek (public)
Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');

// Authenticated features
Route::middleware('auth')->group(function () {
    // Klien pasang proyek
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

    // Developer ajukan penawaran
    Route::post('/bids', [BidController::class, 'store'])->name('bids.store');

    // Klien memilih pemenang
    Route::post('/bids/pick', [BidController::class, 'pickWinner'])->name('bids.pick');

    // Dasbor (Klien & Developer)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Panel
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/users/{id}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::delete('/projects/{id}', [AdminController::class, 'deleteProject'])->name('projects.delete');
    });
});
