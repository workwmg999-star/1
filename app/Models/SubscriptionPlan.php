<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'max_storage_gb',
        'max_users',
        'max_documents',
        'max_file_size_mb',
        'price_monthly',
        'price_yearly',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'float',
        'price_yearly' => 'float',
        'max_storage_gb' => 'integer',
        'max_users' => 'integer',
        'max_documents' => 'integer',
        'max_file_size_mb' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'plan_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isUnlimitedUsers(): bool
    {
        return $this->max_users === -1;
    }

    public function isUnlimitedDocuments(): bool
    {
        return $this->max_documents === -1;
    }

    public function isFree(): bool
    {
        return $this->price_monthly == 0;
    }

    public function getStorageLimitBytesAttribute(): int
    {
        return $this->max_storage_gb * (1024 ** 3);
    }
}
