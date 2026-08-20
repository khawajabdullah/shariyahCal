<?php

use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\LanguageController as AdminLanguageController;
use App\Http\Controllers\Api\Admin\MadhhabController as AdminMadhhabController;
use App\Http\Controllers\Api\Admin\ProfileController;
use App\Http\Controllers\Api\Admin\ScholarController as AdminScholarController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\ScholarController;
use App\Http\Controllers\Api\Webhook\CalComWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:admin-login');
Route::post('/logout', [LogoutController::class, 'store'])->middleware('auth:sanctum');
Route::get('/user', [MeController::class, 'show'])->middleware('auth:sanctum');

Route::get('/scholars', [ScholarController::class, 'index']);
Route::get('/scholars/{id}', [ScholarController::class, 'show']);
Route::get('/directory-filters', [ScholarController::class, 'filters']);

Route::post('/webhooks/cal', [CalComWebhookController::class, 'store']);

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:admin-password');

    Route::get('/madhahib/options', [AdminMadhhabController::class, 'options']);
    Route::get('/madhahib', [AdminMadhhabController::class, 'index']);
    Route::post('/madhahib', [AdminMadhhabController::class, 'store']);
    Route::put('/madhahib/{madhhab}', [AdminMadhhabController::class, 'update']);
    Route::delete('/madhahib/{madhhab}', [AdminMadhhabController::class, 'destroy']);

    Route::get('/languages/options', [AdminLanguageController::class, 'options']);
    Route::get('/languages', [AdminLanguageController::class, 'index']);
    Route::post('/languages', [AdminLanguageController::class, 'store']);
    Route::put('/languages/{language}', [AdminLanguageController::class, 'update']);
    Route::delete('/languages/{language}', [AdminLanguageController::class, 'destroy']);

    Route::post('/scholars/sync', [AdminScholarController::class, 'sync']);
    Route::get('/scholars', [AdminScholarController::class, 'index']);
    Route::get('/scholars/{scholar}', [AdminScholarController::class, 'show']);
    Route::put('/scholars/{scholar}', [AdminScholarController::class, 'update']);
    Route::delete('/scholars/{scholar}', [AdminScholarController::class, 'destroy']);

    Route::post('/bookings/sync', [AdminBookingController::class, 'sync']);
    Route::get('/bookings', [AdminBookingController::class, 'index']);
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show']);
});
