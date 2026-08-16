<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\DocumentResource;
use App\Models\ActivityLog;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * GET /api/v1/company/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $company = $request->user()->company->load('plan');

        return response()->json([
            'success' => true,
            'data' => new CompanyResource($company),
        ]);
    }

    /**
     * PUT /api/v1/company/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (! $user->isOwnerOrAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only owners and admins can update company profile.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($company->logo) {
                Storage::delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store(
                "companies/{$company->id}/logo",
                'public'
            );
        } else {
            unset($validated['logo']);
        }

        $company->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Company profile updated.',
            'data' => new CompanyResource($company->fresh()->load('plan')),
        ]);
    }

    /**
     * PUT /api/v1/company/plan
     * Owner only. Switches the company subscription plan.
     */
    public function updatePlan(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (! $user->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the company owner can change the subscription plan.',
            ], 403);
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        if (! $plan->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This plan is not available.',
            ], 422);
        }

        $company->update([
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);

        ActivityLog::log('plan_changed', $company, "Switched to plan {$plan->name}");

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated.',
            'data' => new CompanyResource($company->fresh()->load('plan')),
        ]);
    }

    /**
     * GET /api/v1/company/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company->load('plan');

        $totalDocuments = $company->documents()->count();
        $totalFolders = $company->folders()->count();
        $archivedDocs = $company->documents()->where('is_archived', true)->count();

        // Recent 10 documents
        $recentDocuments = $company->documents()
            ->with(['user', 'folder'])
            ->latest()
            ->limit(10)
            ->get();

        // Documents per type
        $docsByType = $company->documents()
            ->selectRaw('file_type, COUNT(*) as count')
            ->groupBy('file_type')
            ->pluck('count', 'file_type');

        // Storage info
        $storageLimitBytes = $company->plan->max_storage_gb * (1024 ** 3);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_documents' => $totalDocuments,
                    'total_folders' => $totalFolders,
                    'archived_docs' => $archivedDocs,
                    'total_users' => $company->users()->count(),
                    'docs_by_type' => $docsByType,
                ],
                'storage' => [
                    'used_bytes' => $company->storage_used_bytes,
                    'used_gb' => $company->storage_used_gb,
                    'limit_gb' => $company->storage_limit_gb,
                    'usage_percent' => $company->storage_usage_percent,
                    'remaining_bytes' => max(0, $storageLimitBytes - $company->storage_used_bytes),
                ],
                'plan' => [
                    'name' => $company->plan->name,
                    'max_users' => $company->plan->max_users === -1 ? 'unlimited' : $company->plan->max_users,
                    'max_documents' => $company->plan->max_documents === -1 ? 'unlimited' : $company->plan->max_documents,
                    'expires_at' => $company->plan_expires_at?->toISOString(),
                ],
                'recent_documents' => DocumentResource::collection($recentDocuments),
            ],
        ]);
    }
}
