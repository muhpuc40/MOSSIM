<?php

use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\PriceController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        // ── Products ─────────────────────────────────────────────────────────
        Route::resource('products', ProductController::class)->names([
            'index'   => 'products.index',
            'create'  => 'products.create',
            'store'   => 'products.store',
            'show'    => 'products.show',
            'edit'    => 'products.edit',
            'update'  => 'products.update',
            'destroy' => 'products.destroy',
        ]);

        // ── Variant Prices ───────────────────────────────────────────────────
        // GET  /admin/variants/{variant}/prices
        // GET  /admin/variants/{variant}/prices/create
        // POST /admin/variants/{variant}/prices
        Route::prefix('variants/{variant}')->name('variants.')->group(function () {
            Route::get('prices',        [PriceController::class, 'index'])->name('prices.index');
            Route::get('prices/create', [PriceController::class, 'create'])->name('prices.create');
            Route::post('prices',       [PriceController::class, 'store'])->name('prices.store');
        });

        // DELETE /admin/prices/{price}
        Route::delete('prices/{price}', [PriceController::class, 'destroy'])->name('prices.destroy');

        // ── Coupons ──────────────────────────────────────────────────────────
        Route::resource('coupons', CouponController::class)->names([
            'index'   => 'coupons.index',
            'create'  => 'coupons.create',
            'store'   => 'coupons.store',
            'show'    => 'coupons.show',
            'edit'    => 'coupons.edit',
            'update'  => 'coupons.update',
            'destroy' => 'coupons.destroy',
        ]);
    });