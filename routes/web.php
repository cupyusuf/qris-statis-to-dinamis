<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrisController;
use App\Http\Controllers\QrisProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QrisController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('dashboard/qris-profiles', [QrisProfileController::class, 'store'])->name('qris-profiles.store');
    Route::patch('dashboard/qris-profiles/{qrisProfile}', [QrisProfileController::class, 'update'])->name('qris-profiles.update');
    Route::delete('dashboard/qris-profiles/{qrisProfile}', [QrisProfileController::class, 'destroy'])->name('qris-profiles.destroy');
    Route::post('dashboard/qris-profiles/{qrisProfile}/activate', [QrisProfileController::class, 'activate'])->name('qris-profiles.activate');
});

require __DIR__ . '/settings.php';
