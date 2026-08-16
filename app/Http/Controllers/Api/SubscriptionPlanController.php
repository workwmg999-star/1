<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * GET /api/v1/plans
     */
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => SubscriptionPlanResource::collection($plans),
        ]);
    }

    /**
     * GET /api/v1/plans/{plan}
     */
    public function show(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        if (! $plan->is_active && ! $request->user()?->isOwnerOrAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }
}
