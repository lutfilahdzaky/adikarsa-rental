<?php

use App\Http\Controllers\HeavyEquipmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RentalController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified', AdminOnly::class])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('heavy-equipments', HeavyEquipmentController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->except(['show']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rentals', function () {
        return redirect()->route('rentals.create');
    });

    Route::get('/history', [RentalController::class, 'index'])->name('history');
    Route::resource('rentals', RentalController::class)->except(['index']);
});

require __DIR__.'/settings.php';
