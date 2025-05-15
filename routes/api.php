<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuthController;

// Auth
Route::post('/login', [AuthController::class, 'login']);

// Utilisateur
Route::controller(UserController::class)->group(function () {
    Route::post('/users', 'store');
    Route::get('/users/{userId}/accounts', 'accounts');
});

// Comptes & Transactions
Route::controller(TransactionController::class)->group(function () {
    Route::get('/accounts/{accountNumber}/transactions', 'history');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/transfer', 'transfer');
    });
});

// Profil utilisateur (protégé)
Route::middleware('auth:sanctum')->get('/profile', [UserController::class, 'profile']);
