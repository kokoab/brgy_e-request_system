<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentRequestController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Requestor routes - anyone authenticated can request documents
    Route::post('/document-request', [DocumentRequestController::class, 'store']);
    Route::get('/document-requests', [DocumentRequestController::class, 'index']);

    // Staff routes - only staff can approve/reject
    Route::middleware('role:staff')->group(function () {
        Route::post('/document-request/approve', [DocumentRequestController::class, 'approve']);
        Route::post('/document-request/reject', [DocumentRequestController::class, 'reject']);
    });

    // Admin routes - only admin can see overview
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/overview', [DocumentRequestController::class, 'overview']);
    });
});
