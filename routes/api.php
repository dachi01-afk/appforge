<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderMessageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');


Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user');

    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'update'])->parameters([
        'orders' => 'order:order_code',
    ]);

    Route::apiResource('orders.messages', OrderMessageController::class)->only(['index', 'store'])->scoped([
        'order' => 'order_code',
    ]);

    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{payment}/proof', [PaymentController::class, 'submitProof'])->name('payments.submit_proof');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
});
