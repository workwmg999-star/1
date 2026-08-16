<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FolderWebController;
use App\Http\Controllers\Web\DocumentWebController;

// ─── Public Pages ─────────────────────────────────────────────────────────────
Route::get('/', [AuthWebController::class, 'landing'])->name('landing');
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthWebController::class, 'register'])->name('register.post');

// ─── Protected Pages (Auth Middleware) ─────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Folders
    Route::get('/folders', [FolderWebController::class, 'index'])->name('folders.index');
    Route::post('/folders', [FolderWebController::class, 'store'])->name('folders.store');
    Route::delete('/folders/{id}', [FolderWebController::class, 'destroy'])->name('folders.destroy');

    // Scanner Studio
    Route::get('/scan', [DocumentWebController::class, 'scan'])->name('scan');

    // Documents
    Route::get('/documents/search', [DocumentWebController::class, 'search'])->name('documents.search');
    Route::get('/documents/{id}/download', [DocumentWebController::class, 'download'])->name('documents.download');
    Route::get('/documents', [DocumentWebController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentWebController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{id}', [DocumentWebController::class, 'destroy'])->name('documents.destroy');

    // Settings
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::get('/subscriptions', [DashboardController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/subscriptions', [DashboardController::class, 'subscriptions']);
});
