<?php

use App\Http\Controllers\HeavyEquipmentController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('heavy-equipments', HeavyEquipmentController::class);
});

require __DIR__.'/settings.php';
