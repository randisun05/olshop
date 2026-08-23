<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Storefront\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return request()->user()->hasAnyRole(['Super Admin', 'Admin'])
            ? redirect()->route('admin.dashboard')
            : redirect()->route('customer.dashboard');
    })->name('dashboard');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:Super Admin|Admin'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    });

Route::prefix('akun')
    ->name('customer.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
    });
