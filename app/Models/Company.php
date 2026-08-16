<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'country',
        'logo',
        'plan_id',
        'storage_used_bytes',
        'plan_expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'plan_expires_at' => 'datetime',
        'storage_used_bytes' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getStorageUsedGbAttribute(): float
    {
        return round($this->storage_used_bytes / (1024 ** 3), 2);
    }

    public function getStorageUsedMbAttribute(): float
    {
        return round($this->storage_used_bytes / (1024 ** 2), 2);
    }

    public function getStorageLimitGbAttribute(): int
    {
        return $this->plan->max_storage_gb ?? 5;
    }

    public function getStorageUsagePercentAttribute(): float
    {
        $limitBytes = $this->storage_limit_gb * (1024 ** 3);
        if ($limitBytes <= 0) {
            return 0;
        }

        return round(($this->storage_used_bytes / $limitBytes) * 100, 1);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return \Storage::url($this->logo);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hasStorageAvailable(int $bytesToAdd): bool
    {
        $plan = $this->plan;
        if (! $plan) {
            return false;
        }

        $limitBytes = $plan->max_storage_gb * (1024 ** 3);

        return ($this->storage_used_bytes + $bytesToAdd) <= $limitBytes;
    }

    public function incrementStorage(int $bytes): void
    {
        $this->increment('storage_used_bytes', $bytes);
    }

    public function decrementStorage(int $bytes): void
    {
        $newValue = max(0, $this->storage_used_bytes - $bytes);
        $this->update(['storage_used_bytes' => $newValue]);
    }
}
