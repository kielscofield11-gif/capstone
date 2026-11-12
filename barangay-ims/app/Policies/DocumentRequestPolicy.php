<?php

namespace App\Policies;

use App\Models\DocumentRequest;
use App\Models\User;

class DocumentRequestPolicy extends CoreRecordPolicy
{
    public function approve(User $user, DocumentRequest $document): bool
    {
        return $user->hasAnyRole(User::DOCUMENT_PROCESSORS);
    }

    public function release(User $user, DocumentRequest $document): bool
    {
        return $user->hasAnyRole(User::DOCUMENT_PROCESSORS);
    }

    public function cancel(User $user, DocumentRequest $document): bool
    {
        return $user->hasAnyRole(User::DOCUMENT_PROCESSORS);
    }

    public function render(User $user, DocumentRequest $document): bool
    {
        return $this->view($user, $document) && $user->hasAnyRole(User::DOCUMENT_PROCESSORS);
    }
}
