<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_number' => $this->page_number,
            'file_type' => $this->file_type,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'file_url' => $this->file_url,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
