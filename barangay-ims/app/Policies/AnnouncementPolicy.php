<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy extends CoreRecordPolicy
{
    public function update(User $user, $model): bool
    {
        $announcement = $model;

        // Authors may still edit their own drafts even outside the editor roles.
        if ($announcement->status === 'draft' && $announcement->created_by === $user->id) {
            return true;
        }

        return $user->hasAnyRole(User::CORE_EDITORS);
    }

    public function delete(User $user, $model): bool
    {
        return $user->isAdmin();
    }

    public function publish(User $user, Announcement $announcement): bool
    {
        // Only editors (admin/secretary) may publish; staff authors need one to
        // review their drafts first. Archived announcements may be republished.
        return in_array($announcement->status, [Announcement::STATUS_DRAFT, Announcement::STATUS_ARCHIVED], true)
            && $user->hasAnyRole(User::CORE_EDITORS);
    }

    public function unpublish(User $user, Announcement $announcement): bool
    {
        return $announcement->isPublished() && $user->hasAnyRole(User::CORE_EDITORS);
    }
}
