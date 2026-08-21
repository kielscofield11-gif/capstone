<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NumberSequenceService
{
    public function nextValue(string $sequenceKey, string $prefix, int $year, ?callable $historicalMaximum = null): int
    {
        $this->validate($sequenceKey, $prefix, $year);

        return DB::transaction(function () use ($sequenceKey, $prefix, $year, $historicalMaximum) {
            NumberSequence::query()->insertOrIgnore([
                'sequence_key' => $sequenceKey,
                'prefix' => $prefix,
                'year' => $year,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = NumberSequence::query()
                ->where('sequence_key', $sequenceKey)
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();

            $minimum = $historicalMaximum ? max(1, ((int) $historicalMaximum()) + 1) : 1;
            $current = max((int) $sequence->next_number, $minimum);
            $sequence->update([
                'prefix' => $prefix,
                'next_number' => $current + 1,
            ]);

            return $current;
        }, 3);
    }

    public function nextFormatted(string $sequenceKey, string $prefix, int $year, ?callable $historicalMaximum = null): string
    {
        $number = $this->nextValue($sequenceKey, $prefix, $year, $historicalMaximum);

        return sprintf('%s-%d-%03d', $prefix, $year, $number);
    }

    private function validate(string $sequenceKey, string $prefix, int $year): void
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $sequenceKey)) {
            throw new InvalidArgumentException('Sequence key contains unsupported characters.');
        }

        if (!preg_match('/^[A-Z0-9-]+$/', $prefix)) {
            throw new InvalidArgumentException('Prefix must contain only uppercase letters, numbers, or hyphens.');
        }

        if ($year < 1900 || $year > 9999) {
            throw new InvalidArgumentException('Sequence year is outside the supported range.');
        }
    }
}
