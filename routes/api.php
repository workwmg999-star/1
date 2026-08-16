<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DocuScan SaaS REST API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ─── Public Subscription Plans ──────────────────────────────────────────
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'plans']);

    // ─── Authentication ─────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout',   [AuthController::class, 'logout']);
            Route::get('/me',        [AuthController::class, 'me']);
            Route::put('/profile',   [AuthController::class, 'updateProfile']);
        });
    });

    // ─── Authenticated Company Routes ───────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Company Profile, Dashboard & Users
        Route::prefix('company')->group(function () {
            Route::get('/profile',   [CompanyController::class, 'profile']);
            Route::put('/profile',   [CompanyController::class, 'updateProfile']);
            Route::get('/dashboard', [CompanyController::class, 'dashboard']);

            // Company users route alias
            Route::apiResource('users', UserManagementController::class)->except(['show']);
        });

        // Folders CRUD
        Route::apiResource('folders', FolderController::class)->names('api.folders');

        // Documents Management
        Route::get('/documents/search',            [DocumentController::class, 'search']);
        Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
        Route::apiResource('documents', DocumentController::class)->names('api.documents');

        // Team / User Management (Direct alias)
        Route::apiResource('users', UserManagementController::class)->except(['show']);

        // Subscription Status & Upgrades
        Route::prefix('subscriptions')->group(function () {
            Route::get('/current', [SubscriptionController::class, 'current']);
            Route::post('/upgrade', [SubscriptionController::class, 'upgrade']);
        });
    });
});
