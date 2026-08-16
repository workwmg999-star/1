<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'file_type' => $this->file_type,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'size_formatted' => $this->size_formatted,
            'pages_count' => $this->pages_count,
            'is_pdf' => $this->is_pdf,
            'is_archived' => $this->is_archived,
            'thumbnail_url' => $this->thumbnail_url,
            'file_url' => $this->file_url,
            'metadata' => $this->metadata,
            'folder_id' => $this->folder_id,
            'folder' => $this->whenLoaded('folder', fn () => new FolderResource($this->folder)),
            'user_id' => $this->user_id,
            'uploaded_by' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'files' => DocumentFileResource::collection($this->whenLoaded('files')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
