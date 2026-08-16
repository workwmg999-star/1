<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /api/v1/company/users
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorizeManage($user);

        $users = User::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * POST /api/v1/company/users
     * Limit checked by middleware subscription.limits:users.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->authorizeManage($user);

        $employee = User::create([
            'company_id' => $user->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => true,
        ]);

        ActivityLog::log('user_created', $employee, "Created user {$employee->name} ({$employee->role})");

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => new UserResource($employee),
        ], 201);
    }

    /**
     * GET /api/v1/company/users/{user}
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorizeManage($request->user());

        if ($user->company_id !== $request->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * PUT /api/v1/company/users/{user}
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        $this->authorizeManage($authUser);

        if ($user->company_id !== $authUser->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $validated = $request->validated();

        // Cannot deactivate or demote yourself
        if ($user->id === $authUser->id) {
            $validated['is_active'] = true;
            if (isset($validated['role']) && $validated['role'] !== $user->role) {
                unset($validated['role']);
            }
        }

        // Prevent removing the last owner
        if ($user->isOwner() && isset($validated['role']) && $validated['role'] !== User::ROLE_OWNER) {
            $ownerCount = User::where('company_id', $user->company_id)
                ->where('role', User::ROLE_OWNER)
                ->count();

            if ($ownerCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'A company must keep at least one owner.',
                ], 422);
            }
        }

        $user->update($validated);

        ActivityLog::log('user_updated', $user, "Updated user {$user->name}", [
            'changes' => $validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated.',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/company/users/{user}
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        $this->authorizeManage($authUser);

        if ($user->company_id !== $authUser->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->id === $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        if ($user->isOwner()) {
            $ownerCount = User::where('company_id', $user->company_id)
                ->where('role', User::ROLE_OWNER)
                ->count();

            if ($ownerCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'A company must keep at least one owner.',
                ], 422);
            }
        }

        ActivityLog::log('user_deleted', $user, "Deleted user {$user->name}");

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted.',
        ]);
    }

    private function authorizeManage(User $user): void
    {
        abort_unless($user->isOwnerOrAdmin(), 403, 'Only owners and admins can manage users.');
    }
}
