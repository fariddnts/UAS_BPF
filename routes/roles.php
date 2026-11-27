<?php

use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    foreach (User::ROLES as $role) {
        Route::get("/{$role}/dashboard", [DashboardController::class, 'index'])
            ->middleware("role:{$role}")
            ->name("{$role}.dashboard");
    }
});
