<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Any authenticated user of the same company can view documents.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }

    /**
     * Any authenticated user of the same company can create documents.
     */
    public function create(User $user): bool
    {
        return $user->company_id !== null && $user->is_active;
    }

    /**
     * Owner, admin, or the document creator can update.
     */
    public function update(User $user, Document $document): bool
    {
        if ($user->company_id !== $document->company_id) {
            return false;
        }

        return $user->isOwnerOrAdmin() || $user->id === $document->user_id;
    }

    /**
     * Owner, admin, or the document creator can delete.
     */
    public function delete(User $user, Document $document): bool
    {
        if ($user->company_id !== $document->company_id) {
            return false;
        }

        return $user->isOwnerOrAdmin() || $user->id === $document->user_id;
    }

    /**
     * Any user of the same company can download.
     */
    public function download(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }
}
