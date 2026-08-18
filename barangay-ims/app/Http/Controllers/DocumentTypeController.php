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
        $documentTypes = DocumentType::withCount('documentRequests')->latest()->paginate(10)->withQueryString();
        return view('documents.types.index', compact('documentTypes'));
    }

    public function create()
    {
        return view('documents.types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types',
            'description' => 'nullable|string',
            'fee_amount' => 'required|numeric|min:0',
        ]);

        $documentType = DocumentType::create($validated);

        $this->logAudit('created', DocumentType::class, "Created document type {$documentType->name}", $documentType->id);

        return redirect()->route('document-types.index')->with('success', 'Document type created successfully.');
    }

    public function edit(DocumentType $documentType)
    {
        return view('documents.types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,' . $documentType->id,
            'description' => 'nullable|string',
            'fee_amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $documentType->update($validated);

        $this->logAudit('updated', DocumentType::class, "Updated document type {$documentType->name}", $documentType->id);

        return redirect()->route('document-types.index')->with('success', 'Document type updated successfully.');
    }

    public function destroy(DocumentType $documentType)
    {
        if ($documentType->documentRequests()->count() > 0) {
            return back()->with('error', 'Cannot delete document type with existing requests. Deactivate instead.');
        }

        $name = $documentType->name;
        $documentType->delete();

        $this->logAudit('deleted', DocumentType::class, "Deleted document type {$name}", $documentType->id);

        return redirect()->route('document-types.index')->with('success', 'Document type deleted successfully.');
    }
}
