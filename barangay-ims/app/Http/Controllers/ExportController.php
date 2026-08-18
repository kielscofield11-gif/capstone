<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\Resident;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function residentsPdf(Request $request)
    {
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

        $pdf = Pdf::loadView('exports.residents-pdf', compact('residents', 'totalCount'));
        return $pdf->download('residents-report.pdf');
    }

    public function blottersPdf(Request $request)
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

        $pdf = Pdf::loadView('exports.blotters-pdf', compact('blotters', 'totalCount'));
        return $pdf->download('blotters-report.pdf');
    }

    public function documentsPdf(Request $request)
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

        $pdf = Pdf::loadView('exports.documents-pdf', compact('documents', 'totalCount', 'totalFees'));
        return $pdf->download('documents-report.pdf');
    }

    public function clearancePdf(Resident $resident)
    {
        $resident->load('household');
        $pdf = Pdf::loadView('exports.clearance-pdf', compact('resident'));
        return $pdf->download("clearance-{$resident->id}.pdf");
    }
}
