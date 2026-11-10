<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/app/user', fn () => response()->json(['user' => Auth::user()]));

Route::get('/data/shops', [StorefrontController::class, 'index'])->name('storefront.data.index');
Route::get('/data/shops/{shop:slug}', [StorefrontController::class, 'show'])->name('storefront.data.show');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::prefix('admin/data')->name('admin.data.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/shops', [AdminController::class, 'shops'])->name('shops.index');
    Route::post('/shops', [AdminController::class, 'storeShop'])->name('shops.store');
    Route::put('/shops/{shop}', [AdminController::class, 'updateShop'])->name('shops.update');
    Route::delete('/shops/{shop}', [AdminController::class, 'destroyShop'])->name('shops.destroy');

    Route::get('/products', [AdminController::class, 'products'])->name('products.index');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');

    Route::get('/owners', [AdminController::class, 'owners'])->name('owners.index');
    Route::post('/owners', [AdminController::class, 'storeOwner'])->name('owners.store');
});

Route::prefix('owner/data')->name('owner.data.')->middleware(['auth', 'shop-owner'])->group(function () {
    Route::get('/', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/shops/{shop}/products', [OwnerController::class, 'products'])->name('products.index');
    Route::post('/shops/{shop}/products', [OwnerController::class, 'storeListing'])->name('products.store');
    Route::post('/shops/{shop}/products/new', [OwnerController::class, 'storeNewProduct'])->name('products.store-new');
    Route::put('/shops/{shop}/products/{listing}', [OwnerController::class, 'updateListing'])->name('products.update');
    Route::delete('/shops/{shop}/products/{listing}', [OwnerController::class, 'destroyListing'])->name('products.destroy');
});

Route::view('/', 'app')->name('app');
Route::view('/{path}', 'app')->where('path', '.*');
