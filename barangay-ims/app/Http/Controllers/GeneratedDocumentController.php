<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Services\DocumentTemplateRenderer;
use Barryvdh\DomPDF\Facade\Pdf;

class GeneratedDocumentController extends Controller
{
    public function preview(DocumentRequest $document, DocumentTemplateRenderer $renderer)
    {
        $this->authorize('render', $document);
        $rendered = $renderer->render($document);
        return view('documents.generated', compact('document', 'rendered'));
    }

    public function pdf(DocumentRequest $document, DocumentTemplateRenderer $renderer)
    {
        $this->authorize('render', $document);
        $rendered = $renderer->render($document);
        return Pdf::loadView('documents.generated-pdf', compact('document', 'rendered'))
            ->setPaper('a4', 'portrait')->download("{$document->control_number}.pdf");
    }
}
