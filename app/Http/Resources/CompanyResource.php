<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'country' => $this->country,
            'logo_url' => $this->logo_url,
            'is_active' => $this->is_active,
            'plan' => $this->whenLoaded('plan', fn () => new SubscriptionPlanResource($this->plan)),
            'plan_expires_at' => $this->plan_expires_at?->toISOString(),
            'storage' => [
                'used_bytes' => $this->storage_used_bytes,
                'used_mb' => $this->storage_used_mb,
                'used_gb' => $this->storage_used_gb,
                'limit_gb' => $this->storage_limit_gb,
                'usage_percent' => $this->storage_usage_percent,
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
