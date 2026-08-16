<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function view(User $user, Folder $folder): bool
    {
        return $user->company_id === $folder->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null && $user->is_active;
    }

    public function update(User $user, Folder $folder): bool
    {
        if ($user->company_id !== $folder->company_id) {
            return false;
        }

        return $user->isOwnerOrAdmin() || $user->id === $folder->user_id;
    }

    public function delete(User $user, Folder $folder): bool
    {
        if ($user->company_id !== $folder->company_id) {
            return false;
        }

        return $user->isOwnerOrAdmin() || $user->id === $folder->user_id;
    }
}
