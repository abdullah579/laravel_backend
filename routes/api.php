<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Models\Article;
use App\Models\User;
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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);

    // User management (admin only)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);

    // Articles
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/mine', [ArticleController::class, 'mine']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);
    Route::patch('/articles/{id}/publish', [ArticleController::class, 'publish']);

    // Test routes (can be removed in production)
    Route::get('/test-auth', function (Request $request) {
        return response()->json([
            'message' => 'Authentication successful',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
    });

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

    Route::get('/test-permissions', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'message' => 'Gates and Policies test',
            'gates' => [
                'view-all-users' => $user->can('view-all-users'),
                'assign-roles' => $user->can('assign-roles'),
                'create-article' => $user->can('create-article'),
                'publish-article' => $user->can('publish-article'),
                'delete-article' => $user->can('delete-article'),
                'view-published' => $user->can('view-published'),
                'manage-users' => $user->can('manage-users'),
                'manage-all-articles' => $user->can('manage-all-articles'),
                'is-admin' => $user->can('is-admin'),
                'is-editor' => $user->can('is-editor'),
                'is-author' => $user->can('is-author'),
            ],
            'policies' => [
                'can_view_any_articles' => $user->can('viewAny', Article::class),
                'can_create_articles' => $user->can('create', Article::class),
                'can_view_any_users' => $user->can('viewAny', User::class),
                'can_create_users' => $user->can('create', User::class),
            ],
        ]);
    });
});
