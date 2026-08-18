<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidentController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $query = Resident::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('purok')) {
            $query->where('purok', $request->purok);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('civil_status')) {
            $query->where('civil_status', $request->civil_status);
        }

        if ($request->filled('senior')) {
            $query->where('is_senior', true);
        }

        if ($request->filled('pwd')) {
            $query->where('is_pwd', true);
        }

        if ($request->filled('voter')) {
            $query->where('is_voter', true);
        }

        if ($request->filled('household_id')) {
            $query->where('household_id', $request->household_id);
        }

        $residents = $query->with('household')->latest()->paginate(15)->withQueryString();
        $puroks = Resident::whereNotNull('purok')->distinct()->pluck('purok')->sort();
        $households = Household::where('is_active', true)->get();

        return view('residents.index', compact('residents', 'puroks', 'households'));
    }

    public function create(Request $request)
    {
        $households = Household::where('is_active', true)->get();
        $preselectedHouseholdId = $request->query('household_id');
        return view('residents.create', compact('households', 'preselectedHouseholdId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'birth_date' => 'required|date',
            'birthplace' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'civil_status' => 'required|in:single,married,widowed,separated',
            'occupation' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'blood_type' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'purok' => 'nullable|string|max:100',
            'street_address' => 'nullable|string|max:255',
            'is_voter' => 'boolean',
            'is_pwd' => 'boolean',
            'is_senior' => 'boolean',
            'is_4ps' => 'boolean',
            'household_id' => 'nullable|exists:households,id',
            'is_household_head' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $resident = Resident::create($validated);

        $this->logAudit('created', Resident::class, "Created resident {$resident->full_name}", $resident->id);

        return redirect()->route('residents.index')->with('success', 'Resident added successfully.');
    }

    public function show(Resident $resident)
    {
        $resident->load(['household', 'complaints' => function ($q) {
            $q->latest();
        }, 'responses' => function ($q) {
            $q->latest();
        }, 'documentRequests' => function ($q) {
            $q->latest();
        }, 'documentRequests.documentType']);

        return view('residents.show', compact('resident'));
    }

    public function edit(Resident $resident)
    {
        $households = Household::where('is_active', true)->get();
        return view('residents.edit', compact('resident', 'households'));
    }

    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'birth_date' => 'required|date',
            'birthplace' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'civil_status' => 'required|in:single,married,widowed,separated',
            'occupation' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'blood_type' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'purok' => 'nullable|string|max:100',
            'street_address' => 'nullable|string|max:255',
            'is_voter' => 'boolean',
            'is_pwd' => 'boolean',
            'is_senior' => 'boolean',
            'is_4ps' => 'boolean',
            'household_id' => 'nullable|exists:households,id',
            'is_household_head' => 'boolean',
        ]);

        $resident->update($validated);

        $this->logAudit('updated', Resident::class, "Updated resident {$resident->full_name}", $resident->id);

        return redirect()->route('residents.index')->with('success', 'Resident updated successfully.');
    }

    public function destroy(Resident $resident)
    {
        $name = $resident->full_name;
        $resident->delete();

        $this->logAudit('deleted', Resident::class, "Deleted resident {$name}", $resident->id);

        return redirect()->route('residents.index')->with('success', 'Resident deleted successfully.');
    }
}
