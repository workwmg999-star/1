<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\ActivityLog;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * GET /api/v1/subscriptions/plans
     * List all public available subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => SubscriptionPlanResource::collection($plans),
        ]);
    }

    /**
     * GET /api/v1/subscriptions/current
     * Get current company's subscription status & limits.
     */
    public function current(Request $request): JsonResponse
    {
        $company = $request->user()->company->load('plan');
        $plan    = $company->plan;

        return response()->json([
            'success' => true,
            'data'    => [
                'plan'               => new SubscriptionPlanResource($plan),
                'plan_expires_at'    => $company->plan_expires_at?->toISOString(),
                'usage' => [
                    'storage_used_bytes' => $company->storage_used_bytes,
                    'storage_used_gb'    => $company->storage_used_gb,
                    'storage_limit_gb'   => $plan->max_storage_gb,
                    'storage_percent'    => $company->storage_usage_percent,
                    'total_documents'    => $company->documents()->count(),
                    'max_documents'      => $plan->max_documents === -1 ? 'unlimited' : $plan->max_documents,
                    'total_users'        => $company->users()->count(),
                    'max_users'          => $plan->max_users === -1 ? 'unlimited' : $plan->max_users,
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/subscriptions/upgrade
     * Change or upgrade the company plan.
     */
    public function upgrade(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Only company owners can modify subscription plans.',
            ], 403);
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        $newPlan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $company = $user->company;

        // Check if current usage exceeds new plan's storage limit
        $newLimitBytes = $newPlan->max_storage_gb * (1024 ** 3);
        if ($company->storage_used_bytes > $newLimitBytes) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot downgrade: Current storage usage exceeds the selected plan capacity.',
            ], 422);
        }

        $company->update([
            'plan_id'         => $newPlan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);

        ActivityLog::log('plan_upgraded', $newPlan, "Changed subscription plan to {$newPlan->name}");

        return response()->json([
            'success' => true,
            'message' => "Successfully switched to {$newPlan->name} plan.",
            'data'    => new SubscriptionPlanResource($newPlan),
        ]);
    }
}
