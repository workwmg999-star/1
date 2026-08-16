<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'max_storage_gb' => $this->max_storage_gb,
            'max_users' => $this->max_users === -1 ? 'unlimited' : $this->max_users,
            'max_documents' => $this->max_documents === -1 ? 'unlimited' : $this->max_documents,
            'max_file_size_mb' => $this->max_file_size_mb,
            'price_monthly' => $this->price_monthly,
            'price_yearly' => $this->price_yearly,
            'features' => $this->features ?? [],
            'is_free' => $this->isFree(),
            'sort_order' => $this->sort_order,
        ];
    }
}
