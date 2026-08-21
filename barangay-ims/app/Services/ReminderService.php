<?php

namespace App\Services;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderService
{
    public const RECIPIENT_ROLES = [User::ROLE_ADMIN, User::ROLE_SECRETARY, User::ROLE_CAPTAIN, User::ROLE_KAGAWAD];

    public function generate(?Carbon $date = null): int
    {
        $date ??= today();
        $recipients = User::query()->where('is_active', true)->whereIn('role', self::RECIPIENT_ROLES)->get();
        $created = 0;

        DocumentRequest::query()->pending()->with(['resident', 'documentType'])->each(function (DocumentRequest $request) use ($recipients, $date, &$created) {
            $dueDate = $request->documentType?->processing_days !== null
                ? $request->created_at->copy()->startOfDay()->addDays($request->documentType->processing_days)
                : null;
            $category = $dueDate && $date->greaterThanOrEqualTo($dueDate) ? 'document_overdue' : 'document_pending';
            $title = $category === 'document_overdue' ? 'Document request needs attention' : 'Pending document request';
            $message = "{$request->control_number} for {$request->resident?->full_name} remains pending.";

            foreach ($recipients as $user) {
                $created += $this->insert($user, $category, 'document_request', $request->id, $date, $title, $message, route('documents.show', $request));
            }
        });

        Blotter::query()->whereNotIn('status', ['resolved', 'dismissed'])
            ->hearingsBetween($date, $date->copy()->addDays(3))->with(['complainant', 'respondent'])
            ->each(function (Blotter $blotter) use ($recipients, $date, &$created) {
                $days = (int) $date->diffInDays($blotter->hearing_date, false);
                $when = match ($days) { 0 => 'today', 1 => 'tomorrow', default => "in {$days} days" };
                foreach ($recipients as $user) {
                    $created += $this->insert($user, 'blotter_hearing', 'blotter', $blotter->id, $date, 'Upcoming blotter hearing', "{$blotter->blotter_number} has a hearing {$when}.", route('blotters.show', $blotter));
                }
            });

        return $created;
    }

    private function insert(User $user, string $category, string $recordType, int $recordId, Carbon $date, string $title, string $message, string $url): int
    {
        $key = implode(':', [$category, $recordType, $recordId, $date->toDateString(), $user->id]);
        $hash = md5($key);
        $id = substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-'.substr($hash, 12, 4).'-'.substr($hash, 16, 4).'-'.substr($hash, 20, 12);

        return DB::table('notifications')->insertOrIgnore([
            'id' => $id,
            'type' => 'operational_reminder',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(compact('key', 'category', 'recordType', 'recordId', 'title', 'message', 'url'), JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
