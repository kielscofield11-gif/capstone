<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\Household;
use App\Models\Resident;
use App\Services\ChartDataService;

class DashboardController extends Controller
{
    public function index(ChartDataService $charts)
    {
        $this->authorize('view-reports');
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

        $genderDistribution = $charts->grouped(Resident::query()->whereNotNull('gender'), 'gender')->pluck('total', 'gender');

        $todayResidents = Resident::whereDate('created_at', today())->count();
        $todayBlotters = Blotter::whereDate('created_at', today())->count();
        $todayDocuments = DocumentRequest::whereDate('created_at', today())->count();

        $avgHouseholdSize = $totalHouseholds > 0
            ? round(Resident::count() / $totalHouseholds, 1)
            : 0;

        $recentResidents = Resident::with('household')->latest()->take(5)->get();
        $recentBlotters = Blotter::with(['complainant', 'respondent'])->latest()->take(5)->get();
        $recentDocuments = DocumentRequest::with(['resident', 'documentType'])->latest()->take(5)->get();

        $residentByPurok = $charts->grouped(Resident::query()->whereNotNull('purok'), 'purok')->sortByDesc('total')->values();
        $blotterByStatus = $charts->grouped(Blotter::query(), 'status');
        $documentByStatus = $charts->grouped(DocumentRequest::query(), 'status');
        $residentsByMonth = $charts->monthly(Resident::query());
        $documentsByMonth = $charts->monthly(DocumentRequest::query());
        $blottersByMonth = $charts->monthly(Blotter::query());

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
