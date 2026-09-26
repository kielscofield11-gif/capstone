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
        $filters = $request->validate([
            'purok' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'age_from' => 'nullable|integer|min:0|max:150',
            'age_to' => 'nullable|integer|min:0|max:150',
        ]);
        $query = Resident::with('household');
        if (! empty($filters['purok'])) $query->where('purok', $filters['purok']);
        if (! empty($filters['gender'])) $query->where('gender', $filters['gender']);
        if (isset($filters['age_from'])) $query->whereDate('birth_date', '<=', now()->subYears((int) $filters['age_from']));
        if (isset($filters['age_to'])) $query->whereDate('birth_date', '>', now()->subYears((int) $filters['age_to'] + 1));
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
        $filters = $request->validate([
            'status' => 'nullable|in:pending,hearing,resolved,dismissed',
            'incident_type' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        $query = Blotter::with(['complainant', 'respondent']);
        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (! empty($filters['incident_type'])) $query->where('incident_type', $filters['incident_type']);
        if (! empty($filters['date_from'])) $query->whereDate('incident_date', '>=', $filters['date_from']);
        if (! empty($filters['date_to'])) $query->whereDate('incident_date', '<=', $filters['date_to']);
        $rows = $query->orderBy('incident_date')->get()->map(fn ($b) => [$b->blotter_number, $b->incident_date?->format('Y-m-d'), $b->incident_type, $b->complainant?->full_name, $b->respondent?->full_name, ucfirst($b->status), $b->hearing_date?->format('Y-m-d'), $b->incident_location]);
        return $excel->download('Blotter Report', ['Number', 'Incident Date', 'Type', 'Complainant', 'Respondent', 'Status', 'Hearing Date', 'Location'], $rows, 'blotters-report.xlsx');
    }

    public function documentsExcel(Request $request, SpreadsheetExportService $excel)
    {
        Gate::authorize('view-reports');
        $filters = $request->validate([
            'status' => 'nullable|in:pending,approved,released,cancelled',
            'document_type_id' => 'nullable|integer|exists:document_types,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        $query = DocumentRequest::with(['resident', 'documentType']);
        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (! empty($filters['document_type_id'])) $query->where('document_type_id', $filters['document_type_id']);
        if (! empty($filters['date_from'])) $query->whereDate('created_at', '>=', $filters['date_from']);
        if (! empty($filters['date_to'])) $query->whereDate('created_at', '<=', $filters['date_to']);
        $rows = $query->oldest()->get()->map(fn ($d) => [$d->control_number, $d->created_at?->format('Y-m-d'), $d->resident?->full_name, $d->documentType?->name, ucfirst($d->status), (float) $d->fee_amount, $d->purpose]);
        return $excel->download('Document Request Report', ['Control Number', 'Requested Date', 'Resident', 'Document Type', 'Status', 'Fee', 'Purpose'], $rows, 'documents-report.xlsx');
    }

    public function residentsPdf(Request $request)
    {
        Gate::authorize('view-reports');
        $filters = $request->validate([
            'purok' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'age_from' => 'nullable|integer|min:0|max:150',
            'age_to' => 'nullable|integer|min:0|max:150',
        ]);
        $query = Resident::query();

        if (! empty($filters['purok'])) {
            $query->where('purok', $filters['purok']);
        }
        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        if (isset($filters['age_from'])) {
            $query->whereDate('birth_date', '<=', now()->subYears((int) $filters['age_from']));
        }
        if (isset($filters['age_to'])) {
            $query->whereDate('birth_date', '>', now()->subYears((int) $filters['age_to'] + 1));
        }

        $residents = $query->with('household')->get();
        $totalCount = $residents->count();

        $pdf = Pdf::loadView('exports.residents-pdf', compact('residents', 'totalCount'))->setPaper('a4', 'landscape');
        return $pdf->download('residents-report.pdf');
    }

    public function blottersPdf(Request $request)
    {
        Gate::authorize('view-reports');
        $filters = $request->validate([
            'status' => 'nullable|in:pending,hearing,resolved,dismissed',
            'incident_type' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        $query = Blotter::with(['complainant', 'respondent']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['incident_type'])) {
            $query->where('incident_type', $filters['incident_type']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('incident_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('incident_date', '<=', $filters['date_to']);
        }

        $blotters = $query->get();
        $totalCount = $blotters->count();

        $pdf = Pdf::loadView('exports.blotters-pdf', compact('blotters', 'totalCount'))->setPaper('a4', 'landscape');
        return $pdf->download('blotters-report.pdf');
    }

    public function documentsPdf(Request $request)
    {
        Gate::authorize('view-reports');
        $filters = $request->validate([
            'status' => 'nullable|in:pending,approved,released,cancelled',
            'document_type_id' => 'nullable|integer|exists:document_types,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        $query = DocumentRequest::with(['resident', 'documentType']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['document_type_id'])) {
            $query->where('document_type_id', $filters['document_type_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
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
