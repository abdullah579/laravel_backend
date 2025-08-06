<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of all users (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        // Check authorization using gate
        if (!Gate::allows('view-all-users')) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $users = User::with('roles')->paginate(15);

        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ]);
    }

    /**
     * Assign role to a user (admin only).
     */
    public function assignRole(Request $request, int $id): JsonResponse
    {
        // Check authorization using gate
        if (!Gate::allows('assign-roles')) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        // Check policy for role assignment
        if (!$request->user()->can('assignRole', $user)) {
            return response()->json([
                'message' => 'Cannot assign roles to this user.',
            ], 403);
        }

        $roleName = $request->role;

        if ($user->assignRole($roleName)) {
            return response()->json([
                'message' => "Role '{$roleName}' assigned successfully",
                'user' => $user->load('roles'),
            ]);
        }

        return response()->json([
            'message' => 'Failed to assign role',
        ], 400);
    }

    /**
     * Get authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'user' => $user,
            'permissions' => [
                'can_manage_users' => $user->canManageUsers(),
                'can_manage_all_articles' => $user->canManageAllArticles(),
                'can_publish_articles' => $user->canPublishArticles(),
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }
}
