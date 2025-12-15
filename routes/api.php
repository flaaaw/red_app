<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\User\UserResource;
use App\Models\User;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


use App\Http\Controllers\Api\Auth\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('users', \App\Http\Controllers\Api\UserController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->names('api.user');
Route::apiResource('transactions', \App\Http\Controllers\Api\TransactionController::class);
Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index'])->middleware('auth:sanctum');

// Image serving route to bypass PHP built-in server static file issues
Route::get('/images/{filename}', [\App\Http\Controllers\Api\ImageController::class, 'show'])->where('filename', '.*');