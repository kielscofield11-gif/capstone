<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\Resident;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlotterController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $query = Blotter::with(['complainant', 'respondent']);

        if ($request->filled('search')) {
            $search = $request->search;
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
        $residents = Resident::orderBy('last_name')->get();
        return view('blotters.create', compact('residents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blotter_number' => 'required|string|max:50|unique:blotters',
            'complainant_id' => 'required|exists:residents,id|different:respondent_id',
            'respondent_id' => 'required|exists:residents,id|different:complainant_id',
            'incident_type' => 'required|string|max:255',
            'incident_date' => 'required|date',
            'incident_location' => 'nullable|string|max:255',
            'details' => 'required|string',
            'status' => 'required|in:pending,hearing,resolved,dismissed',
            'hearing_date' => 'nullable|date|after_or_equal:incident_date',
            'resolution' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        if (in_array($validated['status'], ['resolved', 'dismissed'])) {
            $validated['resolved_by'] = Auth::id();
        }

        $blotter = Blotter::create($validated);

        $this->logAudit('created', Blotter::class, "Created blotter {$blotter->blotter_number}", $blotter->id);

        return redirect()->route('blotters.index')->with('success', 'Blotter record created successfully.');
    }

    public function show(Blotter $blotter)
    {
        $blotter->load(['complainant', 'respondent', 'resolvedBy', 'createdBy']);
        return view('blotters.show', compact('blotter'));
    }

    public function edit(Blotter $blotter)
    {
        $residents = Resident::orderBy('last_name')->get();
        return view('blotters.edit', compact('blotter', 'residents'));
    }

    public function update(Request $request, Blotter $blotter)
    {
        $validated = $request->validate([
            'blotter_number' => 'required|string|max:50|unique:blotters,blotter_number,' . $blotter->id,
            'complainant_id' => 'required|exists:residents,id|different:respondent_id',
            'respondent_id' => 'required|exists:residents,id|different:complainant_id',
            'incident_type' => 'required|string|max:255',
            'incident_date' => 'required|date',
            'incident_location' => 'nullable|string|max:255',
            'details' => 'required|string',
            'status' => 'required|in:pending,hearing,resolved,dismissed',
            'hearing_date' => 'nullable|date|after_or_equal:incident_date',
            'resolution' => 'nullable|string',
        ]);

        if (in_array($validated['status'], ['resolved', 'dismissed'])) {
            $validated['resolved_by'] = $blotter->resolved_by ?? Auth::id();
        } else {
            $validated['resolved_by'] = null;
        }

        $blotter->update($validated);

        $this->logAudit('updated', Blotter::class, "Updated blotter {$blotter->blotter_number}", $blotter->id);

        return redirect()->route('blotters.index')->with('success', 'Blotter record updated successfully.');
    }

    public function destroy(Blotter $blotter)
    {
        $number = $blotter->blotter_number;
        $blotter->delete();

        $this->logAudit('deleted', Blotter::class, "Deleted blotter {$number}", $blotter->id);

        return redirect()->route('blotters.index')->with('success', 'Blotter record deleted successfully.');
    }
}
