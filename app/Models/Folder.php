<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'parent_id',
        'name',
        'color',
        'icon',
        'description',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getDocumentsCountAttribute(): int
    {
        return $this->documents()->count();
    }

    public function getPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->path.' / '.$this->name;
        }

        return $this->name;
    }
}
