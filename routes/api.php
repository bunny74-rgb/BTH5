<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderReportController;
use App\Http\Controllers\ProductReportController;

Route::middleware('auth.service')->prefix('reports')->group(function () {
    // Báo cáo sản phẩm
    Route::get('/products', [ProductReportController::class, 'index']);
    Route::get('/products/{id}', [ProductReportController::class, 'show']);
    Route::post('/products', [ProductReportController::class, 'store']);
    Route::delete('/products/{id}', [ProductReportController::class, 'destroy']);

    // Báo cáo đơn hàng
    Route::get('/orders', [OrderReportController::class, 'index']);
    Route::get('/orders/{id}', [OrderReportController::class, 'show']);
    Route::post('/orders', [OrderReportController::class, 'store']);
    Route::delete('/orders/{id}', [OrderReportController::class, 'destroy']);
});