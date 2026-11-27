<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
require __DIR__.'/roles.php';

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Hanya untuk yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

// Halaman untuk semua role yang sudah login
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/status', [DashboardController::class, 'status'])
        ->middleware('role:user')
        ->name('dashboard.status');

    // User: memesan jadwal servis
    Route::get('/booking/create', [BookingController::class, 'create'])
        ->middleware('role:user')
        ->name('booking.create');

    Route::post('/booking', [BookingController::class, 'store'])
        ->middleware('role:user')
        ->name('booking.store');

    Route::post('/booking/{booking}/payment', [BookingController::class, 'updatePaymentMethod'])
        ->middleware('role:user')
        ->name('booking.payment');

    // Mekanik: mengubah status pengerjaan
    Route::patch('/pekerjaan/{booking}/status', [BookingController::class, 'updateStatus'])
        ->middleware('role:mekanik')
        ->name('pekerjaan.status');

    Route::middleware('role:user')->group(function () {
        Route::get('/booking/{booking}/payment/qris', [BookingController::class, 'showQrisPayment'])
            ->name('booking.payment.qris');
        Route::post('/booking/{booking}/payment-proof', [BookingController::class, 'uploadPaymentProof'])
            ->name('booking.payment-proof');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/bookings/{booking}/payment-proof', [BookingController::class, 'showPaymentProof'])
            ->name('admin.payment-proof');
        Route::patch('/admin/bookings/{booking}/payment-verification', [BookingController::class, 'updatePaymentVerification'])
            ->name('admin.payment-verification');
    });

    // Admin dan owner: data pelanggan
    Route::middleware('role:admin,owner')->group(function () {
        Route::resource('pelanggan', PelangganController::class);
    });

    // Admin: kelola akun user
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/completed-users', [DashboardController::class, 'completedUsers'])
            ->name('admin.completed-users');
        Route::resource('user', UserController::class)->except('show');
    });
});
