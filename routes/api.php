<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\OrderController;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'update'])->parameters([
        'orders' => 'order:order_code',
    ]);
});
