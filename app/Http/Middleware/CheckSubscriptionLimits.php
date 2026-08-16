<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\SubscriptionLimitService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    public function __construct(
        private SubscriptionLimitService $limitService
    ) {}

    /**
     * @param  string  $limitType  documents | users | storage
     */
    public function handle(Request $request, Closure $next, string $limitType = 'documents'): Response
    {
        $user = $request->user();
        $company = $user?->company;

        if (! $company) {
            return $this->error('No company associated with this account.', Response::HTTP_FORBIDDEN);
        }

        if (! $company->is_active) {
            return $this->error('Your company account is inactive. Please contact support.', Response::HTTP_FORBIDDEN);
        }

        if ($limitType === 'users' && ! $user->isOwnerOrAdmin()) {
            return $this->error('Only owners and admins can manage users.', Response::HTTP_FORBIDDEN);
        }

        $limitError = match ($limitType) {
            'documents' => $this->checkDocumentLimit($company),
            'users' => $this->checkUserLimit($company),
            'storage' => $this->checkStorageLimit($company, $request),
            default => null,
        };

        if ($limitError) {
            return $limitError;
        }

        return $next($request);
    }

    private function checkDocumentLimit(Company $company): ?JsonResponse
    {
        if (! $this->limitService->canAddDocument($company)) {
            return $this->error(
                'Document limit reached. Please upgrade your plan.',
                Response::HTTP_PAYMENT_REQUIRED,
                ['limit' => $company->plan?->max_documents]
            );
        }

        return null;
    }

    private function checkUserLimit(Company $company): ?JsonResponse
    {
        if (! $this->limitService->canAddUser($company)) {
            return $this->error(
                'User limit reached. Please upgrade your plan.',
                Response::HTTP_PAYMENT_REQUIRED,
                ['limit' => $company->plan?->max_users]
            );
        }

        return null;
    }

    private function checkStorageLimit(Company $company, Request $request): ?JsonResponse
    {
        $totalSize = 0;

        if ($request->hasFile('file')) {
            $totalSize += $request->file('file')->getSize();
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $totalSize += $file->getSize();
            }
        }

        if ($totalSize > 0 && ! $this->limitService->canUploadFile($company, $totalSize)) {
            return $this->error(
                'Storage limit exceeded. Please upgrade your plan.',
                Response::HTTP_PAYMENT_REQUIRED,
                [
                    'storage_used_gb' => $company->storage_used_gb,
                    'storage_limit_gb' => $company->storage_limit_gb,
                ]
            );
        }

        return null;
    }

    private function error(string $message, int $status, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
        ], $extra), $status);
    }
}
