<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Resident;
use App\Traits\LogsAudit;
use App\Services\NumberSequenceService;
use App\Services\DocumentTemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    use LogsAudit;
    public function index(Request $request)
    {
        $this->authorize('viewAny', DocumentRequest::class);
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
        $this->authorize('create', DocumentRequest::class);
        $residents = Resident::orderBy('last_name')->get();
        $documentTypes = DocumentType::where('is_active', true)->get();
        return view('documents.create', compact('residents', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DocumentRequest::class);
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'document_type_id' => ['required', Rule::exists('document_types', 'id')->where('is_active', true)],
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',
            'fee_amount' => 'nullable|numeric|min:0',
        ]);

        $documentType = DocumentType::findOrFail($validated['document_type_id']);

        $document = DB::transaction(function () use ($validated, $documentType) {
            $year = now()->year;
            $validated['control_number'] = app(NumberSequenceService::class)->nextFormatted(
                'document_control', 'DC', $year,
                fn () => $this->historicalMaximum(DocumentRequest::query()->whereYear('created_at', $year)->pluck('control_number'), 'DC', $year)
            );
            $validated['fee_amount'] = $validated['fee_amount'] ?? $documentType->fee_amount;
            $validated['requested_by'] = Auth::id();
            return DocumentRequest::create($validated);
        });

        $this->auditCreated($document, "Created document request {$document->control_number}");

        return redirect()->route('documents.show', $document)->with('success', "Document request {$document->control_number} created successfully.");
    }

    public function show(DocumentRequest $document)
    {
        $this->authorize('view', $document);
        $document->load(['resident', 'documentType', 'requestedBy', 'approvedBy']);
        return view('documents.show', compact('document'));
    }

    public function edit(DocumentRequest $document)
    {
        $this->authorize('update', $document);
        $residents = Resident::orderBy('last_name')->get();
        $documentTypes = DocumentType::where('is_active', true)
            ->when(!$document->documentType?->is_active, fn ($query) => $query->orWhere('id', $document->document_type_id))
            ->get();
        return view('documents.edit', compact('document', 'residents', 'documentTypes'));
    }

    public function update(Request $request, DocumentRequest $document)
    {
        $this->authorize('update', $document);
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'document_type_id' => ['required', Rule::exists('document_types', 'id')->where(function ($query) use ($document) {
                $query->where('is_active', true)->orWhere('id', $document->document_type_id);
            })],
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',
            'fee_amount' => 'nullable|numeric|min:0',
        ]);

        $before = $this->auditSnapshot($document);
        $document->update($validated);

        $this->auditUpdated($document, $before, "Updated document request {$document->control_number}");

        return redirect()->route('documents.index')->with('success', 'Document request updated successfully.');
    }

    public function destroy(DocumentRequest $document)
    {
        $this->authorize('delete', $document);
        $number = $document->control_number;
        $before = $this->auditSnapshot($document);
        $document->delete();

        $this->auditDeleted($document, $before, "Deleted document request {$number}");

        return redirect()->route('documents.index')->with('success', 'Document request deleted successfully.');
    }

    public function approve(DocumentRequest $document)
    {
        $this->authorize('approve', $document);
        if (!in_array($document->status, ['pending'])) {
            return back()->with('error', 'Only pending documents can be approved.');
        }

        $before = $this->auditSnapshot($document);
        $document->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_date' => now(),
        ]);

        $this->auditUpdated($document, $before, "Approved document request {$document->control_number}", 'approved');

        return back()->with('success', 'Document request approved.');
    }

    public function release(DocumentRequest $document)
    {
        $this->authorize('release', $document);
        if (!in_array($document->status, ['approved'])) {
            return back()->with('error', 'Only approved documents can be released.');
        }

        $before = $this->auditSnapshot($document);
        $document->loadMissing(['resident.household', 'documentType.documentTemplate']);
        $document->update([
            'status' => 'released',
            'released_date' => now(),
            'issued_document_snapshot' => app(DocumentTemplateRenderer::class)->snapshot($document),
            'issued_at' => now(),
        ]);

        $this->auditUpdated($document, $before, "Released document request {$document->control_number}", 'released');
        $this->auditEvent('document_issued', $document, "Captured issued document snapshot for {$document->control_number}", null, [
            'template_id' => data_get($document->issued_document_snapshot, 'template_id'),
            'template_name' => data_get($document->issued_document_snapshot, 'template_name'),
        ]);

        return back()->with('success', 'Document marked as released.');
    }

    public function cancel(DocumentRequest $document)
    {
        $this->authorize('cancel', $document);
        if (in_array($document->status, ['released'])) {
            return back()->with('error', 'Released documents cannot be cancelled.');
        }

        $before = $this->auditSnapshot($document);
        $document->update(['status' => 'cancelled']);
        $this->auditUpdated($document, $before, "Cancelled document request {$document->control_number}", 'cancelled');
        return back()->with('success', 'Document request cancelled.');
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
