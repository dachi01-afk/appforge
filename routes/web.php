<?php

use Illuminate\Support\Facades\Route;




Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard.index')->name('dashboard');

    Route::livewire('/orders', 'pages::orders.index')->name('orders.index');
    Route::livewire('/orders/{order}', 'pages::orders.show')->name('orders.show');

    Route::livewire('/inbox', 'pages::inbox.index')->name('inbox.index');

    Route::livewire('/payments', 'pages::payments.index')->name('payments.index');
    Route::livewire('/payments/{payment}', 'pages::payments.show')->name('payments.show');

    Route::livewire('/clients', 'pages::clients.index')->name('clients.index');
    Route::livewire('/clients/{user}', 'pages::clients.show')->name('clients.show');
});

require __DIR__ . '/settings.php';
