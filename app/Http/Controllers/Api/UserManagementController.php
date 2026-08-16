<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function __construct(
        private SubscriptionLimitService $limitService
    ) {}

    /**
     * GET /api/v1/users
     * List all team members in the company.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isOwnerOrAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $users = User::where('company_id', $user->company_id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($users),
        ]);
    }

    /**
     * POST /api/v1/users
     * Add a new employee or admin to the company.
     */
    public function store(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        if (!$currentUser->isOwnerOrAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $company = $currentUser->company;

        if (!$this->limitService->canAddUser($company)) {
            return response()->json([
                'success' => false,
                'message' => 'User limit reached for your subscription plan. Please upgrade.',
            ], 402);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])],
            'phone'    => ['nullable', 'string', 'max:30'],
        ]);

        $newUser = User::create([
            'company_id' => $company->id,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => $validated['role'],
            'phone'      => $validated['phone'] ?? null,
            'is_active'  => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team member added successfully.',
            'data'    => new UserResource($newUser),
        ], 201);
    }

    /**
     * PUT /api/v1/users/{user}
     * Update user details or role.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($user->company_id !== $currentUser->company_id) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        if (!$currentUser->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Only company owners can modify team members.',
            ], 403);
        }

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'role'      => ['sometimes', Rule::in([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])],
            'is_active' => ['sometimes', 'boolean'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['nullable', 'string', 'min:8'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/users/{user}
     * Remove employee/admin.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($user->company_id !== $currentUser->company_id) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        if (!$currentUser->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Only company owners can remove team members.',
            ], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete yourself.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User removed successfully.',
        ]);
    }
}
