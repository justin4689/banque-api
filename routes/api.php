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
Route::get('/accounts/{accountNumber}/transactions', [TransactionController::class, 'history']);

Route::controller(TransactionController::class)->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/transfer', 'transfer');
    });
});

Route::controller(TransactionController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/transactions', 'index'); // Liste toutes les transactions
    });

// Profil utilisateur (protégé)
Route::middleware('auth:sanctum')->get('/profile', [UserController::class, 'profile']);
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
