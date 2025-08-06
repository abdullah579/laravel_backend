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
    Route::post('/register-with-role', [AuthController::class, 'registerWithRole']); // Alternative registration
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

    // Test route to verify role management
    Route::get('/test-roles', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'message' => 'Role management test',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->getRoleNames(),
                'is_admin' => $user->isAdmin(),
                'is_editor' => $user->isEditor(),
                'is_author' => $user->isAuthor(),
                'can_manage_users' => $user->canManageUsers(),
                'can_manage_all_articles' => $user->canManageAllArticles(),
                'can_publish_articles' => $user->canPublishArticles(),
            ],
        ]);
    });
    
    Route::prefix('users')->group(function () {
        
    });

    
    Route::prefix('articles')->group(function () {
        
    });

    
    Route::prefix('roles')->group(function () {
        
    });
});
