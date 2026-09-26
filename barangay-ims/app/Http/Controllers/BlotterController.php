<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\Resident;
use App\Traits\LogsAudit;
use App\Services\NumberSequenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlotterController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Blotter::class);

        $request->validate([
            'status' => 'nullable|in:pending,hearing,resolved,dismissed',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Blotter::with(['complainant', 'respondent']);

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('blotter_number', 'like', "%{$search}%")
                  ->orWhere('incident_type', 'like', "%{$search}%")
                  ->orWhere('incident_location', 'like', "%{$search}%");
            });
        }

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

        $blotters = $query->latest()->paginate(15)->withQueryString();
        $incidentTypes = Blotter::distinct()->pluck('incident_type')->sort();

        return view('blotters.index', compact('blotters', 'incidentTypes'));
    }

    public function create()
    {
        $this->authorize('create', Blotter::class);
        $residents = Resident::select('id', 'first_name', 'middle_name', 'last_name', 'suffix')->orderBy('last_name')->orderBy('first_name')->limit(2000)->get();
        return view('blotters.create', compact('residents'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Blotter::class);
        $validated = $request->validate([
            'complainant_id' => 'required|exists:residents,id|different:respondent_id',
            'respondent_id' => 'required|exists:residents,id|different:complainant_id',
            'incident_type' => 'required|string|max:255',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_location' => 'nullable|string|max:255',
            'details' => 'required|string|max:10000',
            'status' => 'required|in:pending,hearing',
            'hearing_date' => 'nullable|date|after_or_equal:incident_date|required_if:status,hearing',
            'resolution' => 'nullable|string|max:10000',
        ]);

        $blotter = DB::transaction(function () use ($validated) {
            $year = now()->year;
            $validated['blotter_number'] = app(NumberSequenceService::class)->nextFormatted(
                'blotter', 'B', $year,
                fn () => $this->historicalMaximum(Blotter::query()->whereYear('created_at', $year)->pluck('blotter_number'), 'B', $year)
            );
            $validated['created_by'] = Auth::id();
            return Blotter::create($validated);
        });

        $this->auditCreated($blotter, "Created blotter {$blotter->blotter_number}");

        return redirect()->route('blotters.show', $blotter)->with('success', "Blotter {$blotter->blotter_number} created successfully.");
    }

    public function show(Blotter $blotter)
    {
        $this->authorize('view', $blotter);
        $blotter->load(['complainant', 'respondent', 'resolvedBy', 'createdBy']);
        return view('blotters.show', compact('blotter'));
    }

    public function edit(Blotter $blotter)
    {
        $this->authorize('update', $blotter);

        if (in_array($blotter->status, ['resolved', 'dismissed'], true)) {
            return redirect()->route('blotters.show', $blotter)->with('error', 'Resolved or dismissed blotters cannot be edited.');
        }

        $residents = Resident::select('id', 'first_name', 'middle_name', 'last_name', 'suffix')->orderBy('last_name')->orderBy('first_name')->limit(2000)->get();
        return view('blotters.edit', compact('blotter', 'residents'));
    }

    public function update(Request $request, Blotter $blotter)
    {
        $this->authorize('update', $blotter);

        if (in_array($blotter->status, ['resolved', 'dismissed'], true)) {
            return back()->with('error', 'Resolved or dismissed blotters cannot be edited.');
        }

        $validated = $request->validate([
            'complainant_id' => 'required|exists:residents,id|different:respondent_id',
            'respondent_id' => 'required|exists:residents,id|different:complainant_id',
            'incident_type' => 'required|string|max:255',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_location' => 'nullable|string|max:255',
            'details' => 'required|string|max:10000',
            'status' => 'required|in:pending,hearing,resolved,dismissed',
            'hearing_date' => 'nullable|date|after_or_equal:incident_date|required_if:status,hearing',
            'resolution' => 'nullable|string|max:10000|required_if:status,resolved,dismissed',
        ]);

        if (in_array($validated['status'], ['resolved', 'dismissed'])) {
            $validated['resolved_by'] = $blotter->resolved_by ?? Auth::id();
        } else {
            $validated['resolved_by'] = null;
        }

        $before = $this->auditSnapshot($blotter);
        $blotter->update($validated);

        $this->auditUpdated($blotter, $before, "Updated blotter {$blotter->blotter_number}");

        return redirect()->route('blotters.index')->with('success', 'Blotter record updated successfully.');
    }

    public function destroy(Blotter $blotter)
    {
        $this->authorize('delete', $blotter);

        if (in_array($blotter->status, ['resolved', 'dismissed'], true)) {
            return back()->with('error', 'Resolved or dismissed blotters cannot be deleted to preserve history.');
        }

        $number = $blotter->blotter_number;
        $before = $this->auditSnapshot($blotter);
        $blotter->delete();

        $this->auditDeleted($blotter, $before, "Deleted blotter {$number}");

        return redirect()->route('blotters.index')->with('success', 'Blotter record deleted successfully.');
    }

    private function historicalMaximum($numbers, string $prefix, int $year): int
    {
        $maximum = 0;
        foreach ($numbers as $number) {
            if (preg_match('/^'.preg_quote($prefix, '/').'-'.$year.'-(\d+)$/', (string) $number, $matches)) {
                $maximum = max($maximum, (int) $matches[1]);
            }
        }
        return $maximum;
    }
}
