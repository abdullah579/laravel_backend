<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes (no middleware required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes that require token
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return response()->json([
                'user' => $request->user()->load('roles'),
            ]);
        });
    });

    // Test route to verify authentication
    Route::get('/test-auth', function (Request $request) {
        return response()->json([
            'message' => 'Authentication successful',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
    });
    
    Route::prefix('users')->group(function () {
        
    });

    
    Route::prefix('articles')->group(function () {
        
    });

    
    Route::prefix('roles')->group(function () {
        
    });
});
