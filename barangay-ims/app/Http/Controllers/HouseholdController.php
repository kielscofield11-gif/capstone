<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HouseholdController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $query = Household::withCount('residents');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('household_number', 'like', "%{$search}%")
                  ->orWhere('purok', 'like', "%{$search}%")
                  ->orWhere('street_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('purok')) {
            $query->where('purok', $request->purok);
        }

        $households = $query->latest()->paginate(15)->withQueryString();
        $puroks = Household::whereNotNull('purok')->distinct()->pluck('purok')->sort();

        return view('households.index', compact('households', 'puroks'));
    }

    public function create()
    {
        return view('households.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'household_number' => 'required|string|max:50|unique:households',
            'purok' => 'nullable|string|max:100',
            'street_address' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();
        $household = Household::create($validated);

        $this->logAudit('created', Household::class, "Created household {$household->household_number}", $household->id);

        return redirect()->route('households.index')->with('success', 'Household created successfully.');
    }

    public function show(Household $household)
    {
        $household->load(['residents' => function ($q) {
            $q->orderByDesc('is_household_head');
        }]);

        return view('households.show', compact('household'));
    }

    public function edit(Household $household)
    {
        return view('households.edit', compact('household'));
    }

    public function update(Request $request, Household $household)
    {
        $validated = $request->validate([
            'household_number' => 'required|string|max:50|unique:households,household_number,' . $household->id,
            'purok' => 'nullable|string|max:100',
            'street_address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $household->update($validated);

        $this->logAudit('updated', Household::class, "Updated household {$household->household_number}", $household->id);

        return redirect()->route('households.index')->with('success', 'Household updated successfully.');
    }

    public function destroy(Household $household)
    {
        if ($household->residents()->count() > 0) {
            return back()->with('error', 'Cannot delete household with existing residents. Remove residents first.');
        }

        $number = $household->household_number;
        $household->delete();

        $this->logAudit('deleted', Household::class, "Deleted household {$number}", $household->id);

        return redirect()->route('households.index')->with('success', 'Household deleted successfully.');
    }
}
