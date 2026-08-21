<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChartDataService
{
    public function grouped(Builder $query, string $column): Collection
    {
        return $query->select($column, DB::raw('COUNT(*) as total'))->groupBy($column)->orderBy($column)->get();
    }

    public function monthly(Builder $query, string $dateColumn = 'created_at', int $months = 6): Collection
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $expression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$dateColumn})"
            : "DATE_FORMAT({$dateColumn}, '%Y-%m')";
        $counts = $query->where($dateColumn, '>=', $start)
            ->selectRaw("{$expression} as period, COUNT(*) as total")
            ->groupBy('period')->pluck('total', 'period');

        return collect(range($months - 1, 0))->map(function ($offset) use ($counts) {
            $month = now()->startOfMonth()->subMonths($offset);
            return (object) ['period' => $month->format('Y-m'), 'label' => $month->format('M Y'), 'total' => (int) ($counts[$month->format('Y-m')] ?? 0)];
        })->values();
    }
}
