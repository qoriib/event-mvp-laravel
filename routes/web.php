<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\CustomerDashboardController;
use App\Http\Controllers\Web\EventListController;
use App\Http\Controllers\Web\EventShowController;
use App\Http\Controllers\Web\EventTransactionController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\OrganizerDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/events', EventListController::class)->name('events.index');
Route::get('/events/{event}', EventShowController::class)->name('events.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/organizer', OrganizerDashboardController::class)
        ->name('organizer.dashboard');
    Route::get('/customer', CustomerDashboardController::class)
        ->name('customer.dashboard');

    Route::post('/customer/profile', [CustomerDashboardController::class, 'updateProfile'])
        ->name('customer.profile.update');
    Route::post('/organizer/profile', [OrganizerDashboardController::class, 'updateProfile'])
        ->name('organizer.profile.update');
    Route::post('/organizer/events', [OrganizerDashboardController::class, 'createEvent'])
        ->name('organizer.events.store');

    Route::post('/events/{event}/purchase', [EventTransactionController::class, 'purchase'])
        ->name('events.purchase');
    Route::post('/transactions/{transaction}/proof', [EventTransactionController::class, 'uploadProof'])
        ->name('transactions.proof');
    Route::post('/transactions/{transaction}/status', [EventTransactionController::class, 'updateStatus'])
        ->name('transactions.status');
});
