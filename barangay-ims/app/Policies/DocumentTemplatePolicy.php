<?php

namespace App\Policies;

use App\Models\DocumentTemplate;
use App\Models\User;

class DocumentTemplatePolicy
{
    public function before(User $user): ?bool { return $user->isAdmin() ? true : null; }
    public function viewAny(User $user): bool { return false; }
    public function view(User $user, DocumentTemplate $template): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, DocumentTemplate $template): bool { return false; }
    public function delete(User $user, DocumentTemplate $template): bool { return false; }
}
