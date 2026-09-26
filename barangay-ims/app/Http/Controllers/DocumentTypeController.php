<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    use LogsAudit;
    public function index()
    {
        $this->authorize('viewAny', DocumentType::class);
        $documentTypes = DocumentType::withCount('documentRequests')->latest()->paginate(10)->withQueryString();
        return view('documents.types.index', compact('documentTypes'));
    }

    public function create()
    {
        $this->authorize('create', DocumentType::class);
        return view('documents.types.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', DocumentType::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string|max:5000',
            'processing_days' => 'nullable|integer|min:0|max:365',
            'fee_amount' => 'required|numeric|min:0|max:9999999.99|decimal:0,2',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $documentType = DocumentType::create($validated);

        $this->auditCreated($documentType, "Created document type {$documentType->name}");

        return redirect()->route('document-types.index')->with('success', 'Document type created successfully.');
    }

    public function edit(DocumentType $documentType)
    {
        $this->authorize('update', $documentType);
        return view('documents.types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $this->authorize('update', $documentType);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,' . $documentType->id,
            'description' => 'nullable|string',
            'requirements' => 'nullable|string|max:5000',
            'processing_days' => 'nullable|integer|min:0|max:365',
            'fee_amount' => 'required|numeric|min:0|max:9999999.99|decimal:0,2',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $before = $this->auditSnapshot($documentType);
        $documentType->update($validated);

        $action = array_key_exists('is_active', $documentType->getChanges())
            ? ($documentType->is_active ? 'activated' : 'deactivated')
            : 'updated';
        $this->auditUpdated($documentType, $before, "Updated document type {$documentType->name}", $action);

        return redirect()->route('document-types.index')->with('success', 'Document type updated successfully.');
    }

    public function destroy(DocumentType $documentType)
    {
        $this->authorize('delete', $documentType);

        try {
            $deleted = \Illuminate\Support\Facades\DB::transaction(function () use ($documentType) {
                $documentType->lockForUpdate();

                if ($documentType->documentRequests()->exists()) {
                    return false;
                }

                $name = $documentType->name;
                $before = $this->auditSnapshot($documentType);
                $documentType->delete();

                $this->auditDeleted($documentType, $before, "Deleted document type {$name}");

                return true;
            });
        } catch (\Illuminate\Database\QueryException) {
            return back()->with('error', 'Cannot delete document type with existing requests. Deactivate instead.');
        }

        if (! $deleted) {
            return back()->with('error', 'Cannot delete document type with existing requests. Deactivate instead.');
        }

        return redirect()->route('document-types.index')->with('success', 'Document type deleted successfully.');
    }
}
