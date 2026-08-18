<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Resident;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $query = DocumentRequest::with(['resident', 'documentType', 'requestedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('control_number', 'like', "%{$search}%")
                  ->orWhereHas('resident', function ($r) use ($search) {
                      $r->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

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

        $documents = $query->latest()->paginate(15)->withQueryString();
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('documents.index', compact('documents', 'documentTypes'));
    }

    public function create()
    {
        $residents = Resident::orderBy('last_name')->get();
        $documentTypes = DocumentType::where('is_active', true)->get();
        return view('documents.create', compact('residents', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'control_number' => 'required|string|max:50|unique:document_requests',
            'resident_id' => 'required|exists:residents,id',
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',
            'fee_amount' => 'nullable|numeric|min:0',
        ]);

        $documentType = DocumentType::findOrFail($validated['document_type_id']);

        $validated['fee_amount'] = $validated['fee_amount'] ?? $documentType->fee_amount;
        $validated['requested_by'] = Auth::id();

        $document = DocumentRequest::create($validated);

        $this->logAudit('created', DocumentRequest::class, "Created document request {$document->control_number}", $document->id);

        return redirect()->route('documents.index')->with('success', 'Document request created successfully.');
    }

    public function show(DocumentRequest $document)
    {
        $document->load(['resident', 'documentType', 'requestedBy', 'approvedBy']);
        return view('documents.show', compact('document'));
    }

    public function edit(DocumentRequest $document)
    {
        $residents = Resident::orderBy('last_name')->get();
        $documentTypes = DocumentType::where('is_active', true)->get();
        return view('documents.edit', compact('document', 'residents', 'documentTypes'));
    }

    public function update(Request $request, DocumentRequest $document)
    {
        $validated = $request->validate([
            'control_number' => 'required|string|max:50|unique:document_requests,control_number,' . $document->id,
            'resident_id' => 'required|exists:residents,id',
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',
            'fee_amount' => 'nullable|numeric|min:0',
        ]);

        $document->update($validated);

        $this->logAudit('updated', DocumentRequest::class, "Updated document request {$document->control_number}", $document->id);

        return redirect()->route('documents.index')->with('success', 'Document request updated successfully.');
    }

    public function destroy(DocumentRequest $document)
    {
        $number = $document->control_number;
        $document->delete();

        $this->logAudit('deleted', DocumentRequest::class, "Deleted document request {$number}", $document->id);

        return redirect()->route('documents.index')->with('success', 'Document request deleted successfully.');
    }

    public function approve(DocumentRequest $document)
    {
        if (!in_array($document->status, ['pending'])) {
            return back()->with('error', 'Only pending documents can be approved.');
        }

        $document->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_date' => now(),
        ]);

        $this->logAudit('updated', DocumentRequest::class, "Approved document request {$document->control_number}", $document->id);

        return back()->with('success', 'Document request approved.');
    }

    public function release(DocumentRequest $document)
    {
        if (!in_array($document->status, ['approved'])) {
            return back()->with('error', 'Only approved documents can be released.');
        }

        $document->update([
            'status' => 'released',
            'released_date' => now(),
        ]);

        $this->logAudit('updated', DocumentRequest::class, "Released document request {$document->control_number}", $document->id);

        return back()->with('success', 'Document marked as released.');
    }

    public function cancel(DocumentRequest $document)
    {
        if (in_array($document->status, ['released'])) {
            return back()->with('error', 'Released documents cannot be cancelled.');
        }

        $document->update(['status' => 'cancelled']);
        $this->logAudit('updated', DocumentRequest::class, "Cancelled document request {$document->control_number}", $document->id);
        return back()->with('success', 'Document request cancelled.');
    }
}
