<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\Household;
use App\Models\Resident;

class DashboardController extends Controller
{
    public function index()
    {
        $totalResidents = Resident::count();
        $totalHouseholds = Household::count();
        $totalBlotters = Blotter::count();
        $pendingBlotters = Blotter::where('status', 'pending')->count();
        $totalDocuments = DocumentRequest::count();
        $pendingDocuments = DocumentRequest::where('status', 'pending')->count();

        $seniorCount = Resident::where('is_senior', true)->count();
        $pwdCount = Resident::where('is_pwd', true)->count();
        $voterCount = Resident::where('is_voter', true)->count();
        $fourPsCount = Resident::where('is_4ps', true)->count();

        $genderDistribution = Resident::select('gender')
            ->whereNotNull('gender')
            ->get()
            ->countBy('gender');

        $todayResidents = Resident::whereDate('created_at', today())->count();
        $todayBlotters = Blotter::whereDate('created_at', today())->count();
        $todayDocuments = DocumentRequest::whereDate('created_at', today())->count();

        $avgHouseholdSize = $totalHouseholds > 0
            ? round(Resident::count() / $totalHouseholds, 1)
            : 0;

        $recentResidents = Resident::latest()->take(5)->get();
        $recentBlotters = Blotter::with(['complainant', 'respondent'])->latest()->take(5)->get();
        $recentDocuments = DocumentRequest::with(['resident', 'documentType'])->latest()->take(5)->get();

        $residentByPurok = Resident::select('purok')
            ->whereNotNull('purok')
            ->get()
            ->countBy('purok')
            ->map(fn ($total, $purok) => (object) ['purok' => $purok, 'total' => $total])
            ->sortByDesc('total')
            ->values();

        $blotterByStatus = Blotter::select('status')
            ->get()
            ->countBy('status')
            ->map(fn ($total, $status) => (object) ['status' => $status, 'total' => $total])
            ->values();

        $documentByStatus = DocumentRequest::select('status')
            ->get()
            ->countBy('status')
            ->map(fn ($total, $status) => (object) ['status' => $status, 'total' => $total])
            ->values();

        $residentsByMonth = Resident::query()
            ->where('created_at', '>=', now()->subMonths(6))
            ->get(['created_at'])
            ->groupBy(fn ($item) => $item->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($items) => [
                'label' => $items->first()->created_at->format('M Y'),
                'total' => $items->count(),
            ])
            ->values();

        $documentsByMonth = DocumentRequest::query()
            ->where('created_at', '>=', now()->subMonths(6))
            ->get(['created_at'])
            ->groupBy(fn ($item) => $item->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($items) => [
                'label' => $items->first()->created_at->format('M Y'),
                'total' => $items->count(),
            ])
            ->values();

        $blottersByMonth = Blotter::query()
            ->where('created_at', '>=', now()->subMonths(6))
            ->get(['created_at'])
            ->groupBy(fn ($item) => $item->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($items) => [
                'label' => $items->first()->created_at->format('M Y'),
                'total' => $items->count(),
            ])
            ->values();

        $blotterResolvedRate = $totalBlotters > 0
            ? round((Blotter::whereIn('status', ['resolved', 'dismissed'])->count() / $totalBlotters) * 100)
            : 0;

        return view('dashboard.index', compact(
            'totalResidents',
            'totalHouseholds',
            'totalBlotters',
            'pendingBlotters',
            'totalDocuments',
            'pendingDocuments',
            'seniorCount',
            'pwdCount',
            'voterCount',
            'fourPsCount',
            'genderDistribution',
            'todayResidents',
            'todayBlotters',
            'todayDocuments',
            'avgHouseholdSize',
            'recentResidents',
            'recentBlotters',
            'recentDocuments',
            'residentByPurok',
            'blotterByStatus',
            'documentByStatus',
            'residentsByMonth',
            'documentsByMonth',
            'blottersByMonth',
            'blotterResolvedRate',
        ));
    }
}
