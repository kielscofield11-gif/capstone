<?php

namespace App\Services;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Retained for future use (reports/backfill). Resident create/edit no longer
 * block on duplicates by design; see ResidentController household-head guard.
 */
class ResidentDuplicateDetector
{
    public function normalizeNamePart(?string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        return Str::lower($value ?? '');
    }

    public function normalizedIdentity(array|Resident $resident): array
    {
        $value = fn (string $field) => $resident instanceof Resident
            ? $resident->getAttribute($field)
            : ($resident[$field] ?? null);

        $birthDate = $value('birth_date');

        return [
            'first_name' => $this->normalizeNamePart($value('first_name')),
            'middle_name' => $this->normalizeNamePart($value('middle_name')),
            'last_name' => $this->normalizeNamePart($value('last_name')),
            'suffix' => $this->normalizeNamePart($value('suffix')),
            'birth_date' => $birthDate instanceof \DateTimeInterface
                ? $birthDate->format('Y-m-d')
                : (string) $birthDate,
        ];
    }

    public function findPotentialDuplicates(array|Resident $resident, ?int $excludeResidentId = null): Collection
    {
        $identity = $this->normalizedIdentity($resident);

        $candidates = Resident::query()
            ->whereDate('birth_date', $identity['birth_date'])
            ->when($excludeResidentId, fn ($query) => $query->whereKeyNot($excludeResidentId))
            ->get();

        return $candidates
            ->filter(fn (Resident $candidate) => $this->normalizedIdentity($candidate) === $identity)
            ->values();
    }
}
