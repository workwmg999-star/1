<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'folder_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'mime_type',
        'size_bytes',
        'pages_count',
        'thumbnail_path',
        'metadata',
        'is_archived',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_archived' => 'boolean',
        'size_bytes' => 'integer',
        'pages_count' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(DocumentFile::class)->orderBy('page_number');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail_path) {
            return null;
        }

        return Storage::url($this->thumbnail_path);
    }

    public function getSizeMbAttribute(): float
    {
        return round($this->size_bytes / (1024 ** 2), 2);
    }

    public function getSizeFormattedAttribute(): string
    {
        if ($this->size_bytes < 1024) {
            return $this->size_bytes.' B';
        } elseif ($this->size_bytes < 1024 ** 2) {
            return round($this->size_bytes / 1024, 1).' KB';
        } elseif ($this->size_bytes < 1024 ** 3) {
            return round($this->size_bytes / (1024 ** 2), 1).' MB';
        }

        return round($this->size_bytes / (1024 ** 3), 2).' GB';
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->file_type === 'pdf';
    }
}
