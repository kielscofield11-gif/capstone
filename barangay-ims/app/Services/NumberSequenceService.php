<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NumberSequenceService
{
    private function nextValue(string $sequenceKey, string $prefix, int $year, ?callable $historicalMaximum = null): int
    {
        $this->validate($sequenceKey, $prefix, $year);

        return DB::transaction(function () use ($sequenceKey, $prefix, $year, $historicalMaximum) {
            // Use a shared lock on the table to prevent race conditions during row creation
            // This is safer than insertOrIgnore which can have race conditions in some DB engines
            $sequence = NumberSequence::query()
                ->where('sequence_key', $sequenceKey)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $minimum = $historicalMaximum ? max(1, ((int) $historicalMaximum()) + 1) : 1;

                try {
                    $sequence = NumberSequence::create([
                        'sequence_key' => $sequenceKey,
                        'prefix' => $prefix,
                        'year' => $year,
                        'next_number' => $minimum + 1,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // A concurrent transaction created the row first. Reload it
                    // (locked) and fall through to the update path below so both
                    // callers hand out sequential, non-duplicate numbers.
                    $sequence = NumberSequence::query()
                        ->where('sequence_key', $sequenceKey)
                        ->where('year', $year)
                        ->lockForUpdate()
                        ->first();

                    if ($sequence) {
                        $current = max((int) $sequence->next_number, $minimum);
                        $sequence->update([
                            'prefix' => $prefix,
                            'next_number' => $current + 1,
                        ]);

                        return $current;
                    }

                    throw $e;
                }

                return $minimum;
            }

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
