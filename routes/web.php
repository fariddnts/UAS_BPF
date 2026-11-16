<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', [HomeController::class, 'index'])->name('home');

// Hanya untuk yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

// Halaman untuk semua role yang sudah login
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User: memesan jadwal servis
    Route::get('/booking/create', [BookingController::class, 'create'])
        ->middleware('role:user')
        ->name('booking.create');

    Route::post('/booking', [BookingController::class, 'store'])
        ->middleware('role:user')
        ->name('booking.store');

    // Mekanik: mengubah status pengerjaan
    Route::patch('/pekerjaan/{booking}/status', [BookingController::class, 'updateStatus'])
        ->middleware('role:mekanik')
        ->name('pekerjaan.status');

    // Admin dan owner: data pelanggan
    Route::middleware('role:admin,owner')->group(function () {
        Route::resource('pelanggan', PelangganController::class);
    });

    // Admin: kelola akun user
    Route::middleware('role:admin')->group(function () {
        Route::resource('user', UserController::class)->except('show');
    });
});
