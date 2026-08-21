<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
use App\Traits\LogsAudit;
use App\Services\ResidentDuplicateDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Resident::class);
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
        $households = Household::where('is_active', true)->with('residents')->get();

        return view('residents.index', compact('residents', 'puroks', 'households'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Resident::class);
        $households = Household::where('is_active', true)->get();
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
            'household_id' => ['nullable', Rule::exists('households', 'id')->where('is_active', true)],
            'is_household_head' => 'boolean',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            'duplicate_override' => 'sometimes|accepted',
        ]);

        $photo = $validated['photo'] ?? null;
        unset($validated['photo'], $validated['duplicate_override']);
        $duplicates = app(ResidentDuplicateDetector::class)->findPotentialDuplicates($validated);
        if ($duplicates->isNotEmpty() && !$request->boolean('duplicate_override')) {
            return back()->withInput()->withErrors(['duplicate_override' => 'A possible duplicate resident was found. Review the matches and explicitly confirm before saving.'])
                ->with('duplicate_candidates', $duplicates->load('household'));
        }

        if ($photo) {
            $validated['photo_path'] = $photo->store('residents/photos', 'public');
        }

        $validated['created_by'] = Auth::id();
        try {
            $resident = Resident::create($validated);
        } catch (\Throwable $exception) {
            if (!empty($validated['photo_path'])) Storage::disk('public')->delete($validated['photo_path']);
            throw $exception;
        }

        $this->auditCreated($resident, "Created resident {$resident->full_name}");
        if ($duplicates->isNotEmpty()) {
            $this->auditEvent('resident_duplicate_override', $resident, "Duplicate warning overridden for resident {$resident->full_name}", null, [
                'candidate_resident_ids' => $duplicates->pluck('id')->all(),
            ]);
        }

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
            ->with('residents')->get();
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
            'household_id' => ['nullable', Rule::exists('households', 'id')->where(function ($query) use ($resident) {
                $query->where('is_active', true)->orWhere('id', $resident->household_id);
            })],
            'is_household_head' => 'boolean',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            'remove_photo' => 'nullable|boolean',
            'duplicate_override' => 'sometimes|accepted',
        ]);

        $photo = $validated['photo'] ?? null;
        $removePhoto = $request->boolean('remove_photo');
        unset($validated['photo'], $validated['remove_photo'], $validated['duplicate_override']);
        $duplicates = app(ResidentDuplicateDetector::class)->findPotentialDuplicates($validated, $resident->id);
        if ($duplicates->isNotEmpty() && !$request->boolean('duplicate_override')) {
            return back()->withInput()->withErrors(['duplicate_override' => 'A possible duplicate resident was found. Review the matches and explicitly confirm before saving.'])
                ->with('duplicate_candidates', $duplicates->load('household'));
        }

        $oldPhoto = $resident->photo_path;
        $newPhoto = $photo?->store('residents/photos', 'public');
        if ($newPhoto) {
            $validated['photo_path'] = $newPhoto;
        } elseif ($removePhoto) {
            $validated['photo_path'] = null;
        }

        $before = $this->auditSnapshot($resident);
        try {
            $resident->update($validated);
        } catch (\Throwable $exception) {
            if ($newPhoto) Storage::disk('public')->delete($newPhoto);
            throw $exception;
        }

        if (($newPhoto || $removePhoto) && $oldPhoto && $oldPhoto !== $resident->photo_path) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $photoAction = $newPhoto ? ($oldPhoto ? 'photo_replaced' : 'photo_added') : ($removePhoto && $oldPhoto ? 'photo_removed' : 'updated');
        $this->auditUpdated($resident, $before, "Updated resident {$resident->full_name}", $photoAction);
        if ($duplicates->isNotEmpty()) {
            $this->auditEvent('resident_duplicate_override', $resident, "Duplicate warning overridden while updating resident {$resident->full_name}", null, [
                'candidate_resident_ids' => $duplicates->pluck('id')->all(),
            ]);
        }

        return redirect()->route('residents.index')->with('success', 'Resident updated successfully.');
    }

    public function destroy(Resident $resident)
    {
        $this->authorize('delete', $resident);
        $name = $resident->full_name;
        $before = $this->auditSnapshot($resident);
        $resident->delete();

        $this->auditDeleted($resident, $before, "Soft-deleted resident {$name}");

        return redirect()->route('residents.index')->with('success', 'Resident deleted successfully.');
    }
}
