<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('organizer', [AuthController::class, 'updateOrganizer']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('organizers/{organizer}', [EventController::class, 'organizerDetail']);
    Route::get('{event}', [EventController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('mine', [EventController::class, 'mine']);
        Route::post('/', [EventController::class, 'store']);
        Route::put('{event}', [EventController::class, 'update']);
    });
});

Route::prefix('transactions')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::post('/', [TransactionController::class, 'store']);
    Route::post('{transaction}/proof', [TransactionController::class, 'uploadProof']);
    Route::put('{transaction}/status', [TransactionController::class, 'updateStatus']);
    Route::get('manage', [TransactionController::class, 'manage']);
});

Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']);
    Route::middleware('auth:sanctum')->post('/', [ReviewController::class, 'store']);
});
