<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PoolController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\RevenueController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function() {
    Route::get('/', fn() => redirect('/dashboard'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Bookings — sales + gm
    Route::resource('bookings', BookingController::class)->middleware('role:sales,gm');

    // Clients — sales + gm
    Route::resource('clients', ClientController::class)->middleware('role:sales,gm');

    // Fleet — operational + gm
    Route::resource('fleet', FleetController::class)->middleware('role:operational,gm');

    // Finance — finance + gm
    Route::resource('finance', FinanceController::class)->only(['index','create','store','show','destroy'])->middleware('role:finance,gm');

    // Pool — operational + gm
    Route::resource('pool', PoolController::class)->middleware('role:operational,gm');

    // Maintenance — operational + gm
    Route::resource('maintenance', MaintenanceController::class)->middleware('role:operational,gm');

    // API endpoints
    Route::prefix('api')->group(function() {
        Route::get('/revenue', [RevenueController::class, 'getRevenue']);
        Route::get('/revenue/per-sales', [RevenueController::class, 'getRevenuePerSales'])->middleware('role:gm');
        Route::get('/client/{client}', [ClientController::class, 'apiShow']);
        Route::get('/booking/{booking}', [BookingController::class, 'apiShow']);
        Route::get('/invoice/{invoice}', [FinanceController::class, 'apiInvoiceShow']);
        Route::get('/vehicle/{fleet}', [FleetController::class, 'apiShow']);
    });
});
