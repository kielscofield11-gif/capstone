<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Resident::class);
        $query = Resident::query();

        if ($request->filled('search')) {
            $search = $this->escapeLike($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('purok')) {
            $query->where('purok', $request->input('purok'));
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->filled('civil_status')) {
            $query->where('civil_status', $request->input('civil_status'));
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
            $query->where('household_id', $request->input('household_id'));
        }

        $residents = $query->with('household')->latest()->paginate(15)->withQueryString();
        $puroks = Resident::whereNotNull('purok')->distinct()->pluck('purok')->sort();
        $households = Household::where('is_active', true)->select('id', 'household_number', 'purok', 'street_address')->with(['residents:id,household_id,first_name,middle_name,last_name,suffix,is_household_head'])->orderBy('household_number')->get();

        return view('residents.index', compact('residents', 'puroks', 'households'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Resident::class);
        $households = Household::where('is_active', true)->select('id', 'household_number', 'purok', 'street_address')->with(['residents:id,household_id,first_name,middle_name,last_name,suffix,is_household_head'])->orderBy('household_number')->get();
        $preselectedHouseholdId = $request->query('household_id');
        return view('residents.create', compact('households', 'preselectedHouseholdId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Resident::class);
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'birth_date' => 'required|date|before_or_equal:today',
            'birthplace' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'civil_status' => 'required|in:single,married,widowed,separated',
            'occupation' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('residents', 'email')->whereNull('deleted_at')],
            'purok' => 'nullable|string|max:100',
            'street_address' => 'nullable|string|max:255',
            'is_voter' => 'boolean',
            'is_pwd' => 'boolean',
            'is_senior' => 'boolean',
            'is_4ps' => 'boolean',
            'household_id' => ['nullable', Rule::exists('households', 'id')->where('is_active', true)],
            'is_household_head' => 'boolean',
        ]);
        $validated = $this->normalizeBooleanFields($request, $validated);

        $validated['created_by'] = Auth::id();

        try {
            $resident = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
                if ($this->householdAlreadyHasHead($validated)) {
                    throw new \App\Exceptions\HouseholdHeadExistsException;
                }

                return Resident::create($validated);
            });
        } catch (\App\Exceptions\HouseholdHeadExistsException) {
            return back()->withInput()->withErrors([
                'is_household_head' => 'This household already has a head. Unassign the current head first.',
            ]);
        }

        $this->auditCreated($resident, "Created resident {$resident->full_name}");

        return redirect()->route('residents.index')->with('success', 'Resident added successfully.');
    }

    public function show(Resident $resident)
    {
        $this->authorize('view', $resident);
        $resident->load(['household.residents', 'complaints' => function ($q) {
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
        $this->authorize('update', $resident);
        $households = Household::where('is_active', true)
            ->when($resident->household_id, fn ($query) => $query->orWhere('id', $resident->household_id))
            ->select('id', 'household_number', 'purok', 'street_address', 'is_active')
            ->with(['residents:id,household_id,first_name,middle_name,last_name,suffix,is_household_head'])
            ->orderBy('household_number')
            ->get();
        return view('residents.edit', compact('resident', 'households'));
    }

    public function update(Request $request, Resident $resident)
    {
        $this->authorize('update', $resident);
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'birth_date' => 'required|date|before_or_equal:today',
            'birthplace' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'civil_status' => 'required|in:single,married,widowed,separated',
            'occupation' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('residents', 'email')->ignore($resident->id)->whereNull('deleted_at')],
            'purok' => 'nullable|string|max:100',
            'street_address' => 'nullable|string|max:255',
            'is_voter' => 'boolean',
            'is_pwd' => 'boolean',
            'is_senior' => 'boolean',
            'is_4ps' => 'boolean',
            'household_id' => ['nullable', Rule::exists('households', 'id')->where(function ($query) use ($resident) {
                $query->where('is_active', true)->orWhere('id', $resident->household_id);
            })],
            'is_household_head' => 'boolean',
        ]);
        $validated = $this->normalizeBooleanFields($request, $validated);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $resident) {
                if ($this->householdAlreadyHasHead($validated, $resident->id)) {
                    throw new \App\Exceptions\HouseholdHeadExistsException;
                }

                $before = $this->auditSnapshot($resident);
                $resident->update($validated);

                $this->auditUpdated($resident, $before, "Updated resident {$resident->full_name}");
            });
        } catch (\App\Exceptions\HouseholdHeadExistsException) {
            return back()->withInput()->withErrors([
                'is_household_head' => 'This household already has a head. Unassign the current head first.',
            ]);
        }

        return redirect()->route('residents.index')->with('success', 'Resident updated successfully.');
    }

    public function destroy(Resident $resident)
    {
        $this->authorize('delete', $resident);

        if ($resident->complaints()->exists() || $resident->responses()->exists() || $resident->documentRequests()->exists()) {
            return back()->with('error', 'Cannot delete resident with blotter or document history. Deactivate or archive instead.');
        }

        $name = $resident->full_name;
        $before = $this->auditSnapshot($resident);
        $resident->delete();

        $this->auditDeleted($resident, $before, "Soft-deleted resident {$name}");

        return redirect()->route('residents.index')->with('success', 'Resident deleted successfully.');
    }

    private function householdAlreadyHasHead(array $validated, ?int $exceptId = null): bool
    {
        if (!($validated['is_household_head'] ?? false) || empty($validated['household_id'])) {
            return false;
        }

        return Resident::where('household_id', $validated['household_id'])
            ->where('is_household_head', true)
            ->when($exceptId, fn ($query) => $query->where('id', '<>', $exceptId))
            ->lockForUpdate()
            ->exists();
    }

    private function normalizeBooleanFields(Request $request, array $validated): array
    {
        foreach (['is_voter', 'is_pwd', 'is_senior', 'is_4ps', 'is_household_head'] as $field) {
            $validated[$field] = $request->boolean($field);
        }

        return $validated;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
