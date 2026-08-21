<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Models\Household;
use App\Services\SpreadsheetExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExportController extends Controller
{
    public function residentsExcel(Request $request, SpreadsheetExportService $excel)
    {
        Gate::authorize('view-reports');
        $query = Resident::with('household');
        if ($request->filled('purok')) $query->where('purok', $request->purok);
        if ($request->filled('gender')) $query->where('gender', $request->gender);
        if ($request->filled('age_from')) $query->whereDate('birth_date', '<=', now()->subYears($request->integer('age_from')));
        if ($request->filled('age_to')) $query->whereDate('birth_date', '>', now()->subYears($request->integer('age_to') + 1));
        $rows = $query->orderBy('last_name')->get()->map(fn ($r) => [$r->id, $r->full_name, $r->birth_date?->format('Y-m-d'), ucfirst($r->gender), $r->purok, $r->street_address, $r->household?->household_number, $r->phone]);
        return $excel->download('Resident Report', ['ID', 'Name', 'Birth Date', 'Gender', 'Purok', 'Address', 'Household', 'Phone'], $rows, 'residents-report.xlsx');
    }

    public function householdsExcel(SpreadsheetExportService $excel)
    {
        Gate::authorize('view-reports');
        $rows = Household::withCount('residents')->orderBy('household_number')->get()->map(fn ($h) => [$h->household_number, $h->purok, $h->street_address, $h->residents_count, $h->is_active ? 'Active' : 'Inactive']);
        return $excel->download('Household Report', ['Household Number', 'Purok', 'Address', 'Members', 'Status'], $rows, 'households-report.xlsx');
    }

    public function blottersExcel(Request $request, SpreadsheetExportService $excel)
    {
        Gate::authorize('view-reports');
        $query = Blotter::with(['complainant', 'respondent']);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('incident_type')) $query->where('incident_type', $request->incident_type);
        if ($request->filled('date_from')) $query->whereDate('incident_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('incident_date', '<=', $request->date_to);
        $rows = $query->orderBy('incident_date')->get()->map(fn ($b) => [$b->blotter_number, $b->incident_date?->format('Y-m-d'), $b->incident_type, $b->complainant?->full_name, $b->respondent?->full_name, ucfirst($b->status), $b->hearing_date?->format('Y-m-d'), $b->incident_location]);
        return $excel->download('Blotter Report', ['Number', 'Incident Date', 'Type', 'Complainant', 'Respondent', 'Status', 'Hearing Date', 'Location'], $rows, 'blotters-report.xlsx');
    }

    public function documentsExcel(Request $request, SpreadsheetExportService $excel)
    {
        Gate::authorize('view-reports');
        $query = DocumentRequest::with(['resident', 'documentType']);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('document_type_id')) $query->where('document_type_id', $request->document_type_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);
        $rows = $query->oldest()->get()->map(fn ($d) => [$d->control_number, $d->created_at?->format('Y-m-d'), $d->resident?->full_name, $d->documentType?->name, ucfirst($d->status), (float) $d->fee_amount, $d->purpose]);
        return $excel->download('Document Request Report', ['Control Number', 'Requested Date', 'Resident', 'Document Type', 'Status', 'Fee', 'Purpose'], $rows, 'documents-report.xlsx');
    }

    public function residentsPdf(Request $request)
    {
        Gate::authorize('view-reports');
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

        $pdf = Pdf::loadView('exports.residents-pdf', compact('residents', 'totalCount'))->setPaper('a4', 'landscape');
        return $pdf->download('residents-report.pdf');
    }

    public function blottersPdf(Request $request)
    {
        Gate::authorize('view-reports');
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

        $pdf = Pdf::loadView('exports.blotters-pdf', compact('blotters', 'totalCount'))->setPaper('a4', 'landscape');
        return $pdf->download('blotters-report.pdf');
    }

    public function documentsPdf(Request $request)
    {
        Gate::authorize('view-reports');
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

        $pdf = Pdf::loadView('exports.documents-pdf', compact('documents', 'totalCount', 'totalFees'))->setPaper('a4', 'landscape');
        return $pdf->download('documents-report.pdf');
    }

    public function clearancePdf(Resident $resident)
    {
        Gate::authorize('view-reports');
        Gate::authorize('view', $resident);
        $resident->load('household');
        $pdf = Pdf::loadView('exports.clearance-pdf', compact('resident'))->setPaper('a4', 'portrait');
        return $pdf->download("clearance-{$resident->id}.pdf");
    }
}
