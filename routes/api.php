<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Products (public) ────────────────────────────────────────────────────
    Route::get('/products',      [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');

    // ── Protected (JWT) ──────────────────────────────────────────────────────
    Route::middleware('auth:api')->group(function () {

        // Coupon validation — called before placing order
        Route::post('/coupons/validate', [CouponController::class, 'validate'])->name('api.coupons.validate');

    });
});