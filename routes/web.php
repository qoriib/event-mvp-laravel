<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EventContentController;
use App\Http\Controllers\EventTransactionController;
use App\Http\Controllers\OrganizerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventContentController::class, 'home'])->name('home');
Route::get('/events', [EventContentController::class, 'list'])->name('events.index');
Route::get('/events/{event}', [EventContentController::class, 'show'])->name('events.show');
Route::get('/organizers/{organizer}', [OrganizerController::class, 'showPublic'])->name('organizers.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/organizer', [OrganizerController::class, 'dashboard'])
        ->name('organizer.dashboard');
    Route::get('/customer', CustomerController::class)
        ->name('customer.dashboard');

    Route::post('/customer/profile', [CustomerController::class, 'updateProfile'])
        ->name('customer.profile.update');
    Route::post('/organizer/profile', [OrganizerController::class, 'updateProfile'])
        ->name('organizer.profile.update');
    Route::post('/organizer/events', [OrganizerController::class, 'storeEvent'])
        ->name('organizer.events.store');
    Route::put('/organizer/events/{event}', [OrganizerController::class, 'updateEvent'])
        ->name('organizer.events.update');
    Route::delete('/organizer/events/{event}', [OrganizerController::class, 'destroyEvent'])
        ->name('organizer.events.destroy');

    Route::post('/events/{event}/purchase', [EventTransactionController::class, 'purchase'])
        ->name('events.purchase');
    Route::post('/transactions/{transaction}/proof', [EventTransactionController::class, 'uploadProof'])
        ->name('transactions.proof');
    Route::post('/transactions/{transaction}/status', [EventTransactionController::class, 'updateStatus'])
        ->name('transactions.status');
Route::post('/events/{event}/reviews', [EventTransactionController::class, 'storeReview'])
        ->name('events.reviews.store');
});
