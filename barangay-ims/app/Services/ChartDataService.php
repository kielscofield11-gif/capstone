<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChartDataService
{
    public function grouped(Builder $query, string $column): Collection
    {
        $this->assertColumn($column);

        return $query->select($column, DB::raw('COUNT(*) as total'))->groupBy($column)->orderBy($column)->get();
    }

    public function monthly(Builder $query, string $dateColumn = 'created_at', int $months = 6): Collection
    {
        $this->assertColumn($dateColumn);
        $wrappedColumn = $query->getModel()->getConnection()->getQueryGrammar()->wrap($dateColumn);
        $start = now()->startOfMonth()->subMonths($months - 1);
        $expression = $this->monthExpression($query, $wrappedColumn);
        $counts = $query->where($dateColumn, '>=', $start)
            ->selectRaw("{$expression} as period, COUNT(*) as total")
            ->groupBy('period')->pluck('total', 'period');

        return collect(range($months - 1, 0))->map(function ($offset) use ($counts) {
            $month = now()->startOfMonth()->subMonths($offset);
            return (object) ['period' => $month->format('Y-m'), 'label' => $month->format('M Y'), 'total' => (int) ($counts[$month->format('Y-m')] ?? 0)];
        })->values();
    }

    private function assertColumn(string $column): void
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_\.]*$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name [{$column}].");
        }
    }

    private function monthExpression(Builder $query, string $wrappedColumn): string
    {
        $driver = $query->getModel()->getConnection()->getDriverName();

        return match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$wrappedColumn})",
            'pgsql' => "TO_CHAR({$wrappedColumn}, 'YYYY-MM')",
            default => "DATE_FORMAT({$wrappedColumn}, '%Y-%m')",
        };
    }
}
