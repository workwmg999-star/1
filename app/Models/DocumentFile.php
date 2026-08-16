<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentFile extends Model
{
    protected $fillable = [
        'document_id',
        'file_path',
        'page_number',
        'file_type',
        'mime_type',
        'size_bytes',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'size_bytes' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
