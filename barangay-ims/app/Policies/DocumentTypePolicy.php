<?php

namespace App\Policies;

use App\Models\DocumentType;
use App\Models\User;

class DocumentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(User::ROLES);
    }

    public function view(User $user, DocumentType $documentType): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, DocumentType $documentType): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, DocumentType $documentType): bool
    {
        return $user->isAdmin();
    }
}
