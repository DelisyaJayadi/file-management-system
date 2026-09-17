<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DashboardController;

// Public Route (Login)
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Membutuhkan Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user()->load('department');
    });

    // Folder Routes
    Route::get('/folders', [FolderController::class, 'index']);
    Route::post('/folders', [FolderController::class, 'store']);
    Route::get('/folders/{id}', [FolderController::class, 'show']);
    Route::delete('/folders/{id}', [FolderController::class, 'destroy']);

    // File Routes
    Route::post('/files', [FileController::class, 'store']);
    Route::get('/files/{id}/download', [FileController::class, 'download']);
    Route::delete('/files/{id}', [FileController::class, 'destroy']);

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Department Routes (CRUD)
    Route::apiResource('/departments', DepartmentController::class);
});
