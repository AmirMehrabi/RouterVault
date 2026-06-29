<?php

use App\Http\Controllers\BackupUploadController;
use App\Http\Controllers\IpamController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Backup upload endpoint (token-based auth, no Laravel auth required)
Route::post('/v1/backups/upload', [BackupUploadController::class, 'upload'])
    ->middleware('throttle:10,1');

Route::middleware('auth')->group(function () {
    // IPAM API Routes
    Route::prefix('ip-pools')->name('api.ip-pools.')->group(function () {
        Route::get('/check-ip', [IpamController::class, 'checkIp'])->name('check-ip');
    });

    // Subscription API Routes
    Route::prefix('subscriptions')->name('api.subscriptions.')->group(function () {
        Route::get('/check-pppoe-username', [SubscriptionController::class, 'checkPppoeUsername'])->name('check-pppoe-username');
    });
});
