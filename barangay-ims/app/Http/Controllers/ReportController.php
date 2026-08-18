<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function residents(Request $request)
    {
        $request->validate([
            'age_from' => 'nullable|integer|min:0|max:150',
            'age_to' => 'nullable|integer|min:0|max:150',
        ]);

        $query = Resident::query();

        if ($request->filled('purok')) {
            $query->where('purok', $request->purok);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('age_from')) {
            $query->whereDate('birth_date', '<=', now()->subYears($request->age_from));
        }

        if ($request->filled('age_to')) {
            $query->whereDate('birth_date', '>', now()->subYears($request->age_to + 1));
        }

        $residents = $query->with('household')->get();
        $totalCount = $residents->count();

        $genderBreakdown = [
            'male' => $residents->where('gender', 'male')->count(),
            'female' => $residents->where('gender', 'female')->count(),
            'other' => $residents->where('gender', 'other')->count(),
        ];

        $civilStatusBreakdown = $residents->groupBy('civil_status')->map->count();
        $maxCivilStatusCount = $civilStatusBreakdown->max() ?: 1;
        $purokBreakdown = $residents->groupBy('purok')->map->count()->sortDesc();
        $seniorCount = $residents->where('is_senior', true)->count();
        $pwdCount = $residents->where('is_pwd', true)->count();
        $voterCount = $residents->where('is_voter', true)->count();
        $fourPsCount = $residents->where('is_4ps', true)->count();

        $puroks = Resident::whereNotNull('purok')->distinct()->pluck('purok')->sort();

        return view('reports.residents', compact(
            'residents', 'totalCount', 'genderBreakdown', 'civilStatusBreakdown',
            'maxCivilStatusCount', 'purokBreakdown', 'seniorCount', 'pwdCount', 'voterCount',
            'fourPsCount', 'puroks'
        ));
    }

    public function blotters(Request $request)
    {
        $query = Blotter::with(['complainant', 'respondent']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('incident_type')) {
            $query->where('incident_type', $request->incident_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('incident_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('incident_date', '<=', $request->date_to);
        }

        $blotters = $query->get();
        $totalCount = $blotters->count();

        $statusBreakdown = $blotters->groupBy('status')->map->count();
        $typeBreakdown = $blotters->groupBy('incident_type')->map->count()->sortDesc();
        $monthlyBreakdown = $blotters->groupBy(function ($b) {
            return $b->incident_date?->format('Y-m') ?? 'unknown';
        })->map->count()->sortKeys();
        $maxBlotterMonthlyCount = $monthlyBreakdown->max() ?: 1;

        $incidentTypes = Blotter::distinct()->pluck('incident_type')->sort();

        return view('reports.blotters', compact(
            'blotters', 'totalCount', 'statusBreakdown', 'typeBreakdown',
            'monthlyBreakdown', 'maxBlotterMonthlyCount', 'incidentTypes'
        ));
    }

    public function documents(Request $request)
    {
        $query = DocumentRequest::with(['resident', 'documentType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $documents = $query->get();
        $totalCount = $documents->count();
        $totalFees = $documents->sum('fee_amount');

        $statusBreakdown = $documents->groupBy('status')->map->count();
        $typeBreakdown = $documents->groupBy('document_type_id')->map->count();
        $typeNames = \App\Models\DocumentType::pluck('name', 'id');

        $monthlyBreakdown = $documents->groupBy(function ($d) {
            return $d->created_at?->format('Y-m') ?? 'unknown';
        })->map->count()->sortKeys();
        $maxDocumentMonthlyCount = $monthlyBreakdown->max() ?: 1;

        $documentTypes = \App\Models\DocumentType::where('is_active', true)->get();

        return view('reports.documents', compact(
            'documents', 'totalCount', 'totalFees', 'statusBreakdown',
            'typeBreakdown', 'typeNames', 'monthlyBreakdown', 'maxDocumentMonthlyCount', 'documentTypes'
        ));
    }
}
