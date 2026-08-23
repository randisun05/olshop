<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/produk', [StorefrontProductController::class, 'index'])->name('catalog');
Route::get('/produk/{slug}', [StorefrontProductController::class, 'show'])->name('catalog.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return request()->user()->hasAnyRole(['Super Admin', 'Admin', 'Staff Gudang'])
            ? redirect()->route('admin.dashboard')
            : redirect()->route('customer.dashboard');
    })->name('dashboard');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:Super Admin|Admin|Staff Gudang'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::middleware('permission:categories.manage')->group(function () {
            Route::resource('categories', CategoryController::class)->except('show');
        });

        Route::middleware('permission:brands.manage')->group(function () {
            Route::resource('brands', BrandController::class)->except('show');
        });

        Route::middleware('permission:attributes.manage')->group(function () {
            Route::resource('attributes', AttributeController::class)->except('show');
        });

        Route::middleware('permission:products.manage')->group(function () {
            Route::resource('products', AdminProductController::class)->except('show');
        });
    });

Route::prefix('akun')
    ->name('customer.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
    });
