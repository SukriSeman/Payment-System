<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('/products', [ProductController::class, 'list'])->name('product.list');

    Route::post('/orders', [OrderController::class, 'create'])->name('order.create');
    Route::get('/orders', [OrderController::class, 'list'])->name('order.list');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('order.show');

    // POST /payments (authorize; idempotent via Idempotency-Key)
    Route::post('/payments', [PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::post('/payments/{id}/capture', [PaymentController::class, 'capture'])->name('payment.capture');
    Route::post('/payments/{id}/void', [PaymentController::class, 'void'])->name('payment.void');

    Route::post('/refunds', [RefundController::class, 'refund'])->name('payment.refund');

    Route::get('/reports/daily-settlement', [ReportsController::class, 'dailySettlement'])->name('report.daily.settlement');
});
