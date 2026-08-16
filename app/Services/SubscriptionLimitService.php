<?php

namespace App\Services;

use App\Models\Company;

class SubscriptionLimitService
{
    /**
     * Check if company can add more documents.
     */
    public function canAddDocument(Company $company): bool
    {
        $plan = $company->plan;
        if (! $plan) {
            return false;
        }

        // -1 means unlimited
        if ($plan->max_documents === -1) {
            return true;
        }

        $count = $company->documents()->count();

        return $count < $plan->max_documents;
    }

    /**
     * Check if company can add more users.
     */
    public function canAddUser(Company $company): bool
    {
        $plan = $company->plan;
        if (! $plan) {
            return false;
        }

        if ($plan->max_users === -1) {
            return true;
        }

        $count = $company->users()->count();

        return $count < $plan->max_users;
    }

    /**
     * Check if company has enough storage for an upload.
     */
    public function canUploadFile(Company $company, int $fileSizeBytes): bool
    {
        $plan = $company->plan;
        if (! $plan) {
            return false;
        }

        $limitBytes = $plan->max_storage_gb * (1024 ** 3);

        return ($company->storage_used_bytes + $fileSizeBytes) <= $limitBytes;
    }

    /**
     * Check if file size is within plan's per-file limit.
     */
    public function isFileSizeAllowed(Company $company, int $fileSizeBytes): bool
    {
        $plan = $company->plan;
        if (! $plan) {
            return false;
        }

        $maxBytes = $plan->max_file_size_mb * (1024 ** 2);

        return $fileSizeBytes <= $maxBytes;
    }

    /**
     * Get remaining storage in bytes.
     */
    public function getRemainingStorageBytes(Company $company): int
    {
        $plan = $company->plan;
        if (! $plan) {
            return 0;
        }

        $limitBytes = $plan->max_storage_gb * (1024 ** 3);

        return max(0, $limitBytes - $company->storage_used_bytes);
    }

    /**
     * Get remaining documents count.
     */
    public function getRemainingDocuments(Company $company): int|string
    {
        $plan = $company->plan;
        if (! $plan) {
            return 0;
        }

        if ($plan->max_documents === -1) {
            return 'unlimited';
        }

        $count = $company->documents()->count();

        return max(0, $plan->max_documents - $count);
    }

    /**
     * Get remaining users count.
     */
    public function getRemainingUsers(Company $company): int|string
    {
        $plan = $company->plan;
        if (! $plan) {
            return 0;
        }

        if ($plan->max_users === -1) {
            return 'unlimited';
        }

        $count = $company->users()->count();

        return max(0, $plan->max_users - $count);
    }
}
