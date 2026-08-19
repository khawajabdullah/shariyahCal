<?php

use App\Http\Controllers\Api\ScholarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/scholars', [ScholarController::class, 'index']);
Route::get('/scholars/{id}', [ScholarController::class, 'show']);
