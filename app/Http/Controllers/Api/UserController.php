<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\AssignRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of all users (admin only).
     */
    public function index(): JsonResponse
    {
        // Check authorization using gate
        if (!Gate::allows('view-all-users')) {
            return ApiResponse::forbidden('Admin access required.');
        }

        $users = User::with('roles')->paginate(15);

        return ApiResponse::paginated($users, 'Users retrieved successfully');
    }

    /**
     * Assign role to a user (admin only).
     */
    public function assignRole(AssignRoleRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $roleName = $request->role;

        if ($user->assignRole($roleName)) {
            return ApiResponse::success(
                $user->load('roles'),
                "Role '{$roleName}' assigned successfully"
            );
        }

        return ApiResponse::error('Failed to assign role');
    }

    /**
     * Get authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        $profileData = [
            'user' => $user,
            'permissions' => [
                'can_manage_users' => $user->canManageUsers(),
                'can_manage_all_articles' => $user->canManageAllArticles(),
                'can_publish_articles' => $user->canPublishArticles(),
                'roles' => $user->getRoleNames(),
            ],
        ];

        return ApiResponse::success($profileData, 'Profile retrieved successfully');
    }
}
