<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use App\Services\DocumentTemplateRenderer;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateController extends Controller
{
    use LogsAudit;

    public function index() { $this->authorize('viewAny', DocumentTemplate::class); return view('document-templates.index', ['templates' => DocumentTemplate::withCount('documentTypes')->latest()->paginate(15)]); }
    public function create() { $this->authorize('create', DocumentTemplate::class); return view('document-templates.form', ['template' => new DocumentTemplate, 'documentTypes' => DocumentType::orderBy('name')->get(), 'placeholders' => DocumentTemplateRenderer::PLACEHOLDERS]); }
    public function edit(DocumentTemplate $documentTemplate) { $this->authorize('update', $documentTemplate); return view('document-templates.form', ['template' => $documentTemplate->load('documentTypes'), 'documentTypes' => DocumentType::orderBy('name')->get(), 'placeholders' => DocumentTemplateRenderer::PLACEHOLDERS]); }

    public function store(Request $request)
    {
        $this->authorize('create', DocumentTemplate::class);
        $data = $this->validated($request);
        $types = $data['document_type_ids'] ?? [];
        unset($data['document_type_ids'], $data['logo']);
        $data['created_by'] = $request->user()->id;
        $logoPath = $request->hasFile('logo') ? $request->file('logo')->store('document-templates/logos', 'public') : null;
        if ($logoPath) {
            $data['logo_path'] = $logoPath;
        }

        try {
            $template = DB::transaction(function () use ($data, $types) {
                $template = DocumentTemplate::create($data);
                $this->assign($template, $types);

                return $template;
            });
        } catch (\Throwable $e) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            throw $e;
        }

        $this->auditCreated($template, "Created document template {$template->name}");

        return redirect()->route('document-templates.edit', $template)->with('success', 'Template created.');
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $this->authorize('update', $documentTemplate);
        $data = $this->validated($request, $documentTemplate);
        if ($documentTemplate->is_default) {
            $data['is_active'] = true;
        }
        $types = $data['document_type_ids'] ?? [];
        unset($data['document_type_ids'], $data['logo']);
        $before = $this->auditSnapshot($documentTemplate);
        $oldLogo = $documentTemplate->logo_path;
        $newLogoPath = $request->hasFile('logo') ? $request->file('logo')->store('document-templates/logos', 'public') : null;
        if ($newLogoPath) {
            // A replacement upload wins over the remove checkbox so the newly
            // stored file is never orphaned.
            $data['logo_path'] = $newLogoPath;
        } elseif ($request->boolean('remove_logo')) {
            $data['logo_path'] = null;
        }

        try {
            DB::transaction(function () use ($documentTemplate, $data, $types) {
                $documentTemplate->update($data);
                $this->assign($documentTemplate, $types);
            });
        } catch (\Throwable $e) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }
            throw $e;
        }

        if ($oldLogo && $oldLogo !== $documentTemplate->logo_path) {
            Storage::disk('public')->delete($oldLogo);
        }
        $this->auditUpdated($documentTemplate, $before, "Updated document template {$documentTemplate->name}", $documentTemplate->is_active ? 'updated' : 'deactivated');

        return back()->with('success', 'Template updated.');
    }

    private function validated(Request $request, ?DocumentTemplate $template=null): array { $id=$template?->id; $data=$request->validate(['name'=>"required|string|max:255|unique:document_templates,name,$id",'title'=>'required|string|max:255','header_line_1'=>'nullable|string|max:255','header_line_2'=>'nullable|string|max:255','header_line_3'=>'nullable|string|max:255','office_name'=>'nullable|string|max:255','barangay_name'=>'nullable|string|max:255','municipality'=>'nullable|string|max:255','province'=>'nullable|string|max:255','opening_phrase'=>'nullable|string|max:2000','body'=>'required|string|max:20000','closing_text'=>'nullable|string|max:5000','signatory_name'=>'nullable|string|max:255','signatory_position'=>'nullable|string|max:255','footer_text'=>'nullable|string|max:5000','logo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048','document_type_ids'=>'nullable|array','document_type_ids.*'=>'integer|distinct|exists:document_types,id']); foreach(['show_control_number','show_fee','show_issue_date','show_resident_photo','show_logo','is_active'] as $key){ $data[$key] = $request->has($key) ? $request->boolean($key) : ($template ? $template->$key : false); } return $data; }
    private function assign(DocumentTemplate $template,array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => is_int($id) || ctype_digit((string) $id))));
        $ids = array_map('intval', $ids);

        \Illuminate\Support\Facades\DB::transaction(function () use ($template, $ids) {
            // Capture the real previous assignment for every type that is being
            // assigned to or removed from this template, so the audit trail
            // records the actual prior template instead of assuming null.
            $previous = DocumentType::where('document_template_id', $template->id)
                ->orWhereIn('id', $ids ?: [0])
                ->lockForUpdate()
                ->pluck('document_template_id', 'id')
                ->all();

            DocumentType::where('document_template_id', $template->id)
                ->whereNotIn('id', $ids ?: [0])
                ->update(['document_template_id' => null]);

            if (! empty($ids)) {
                DocumentType::whereIn('id', $ids)->update(['document_template_id' => $template->id]);
            }

            foreach ($previous as $id => $previousTemplateId) {
                $id = (int) $id;
                $oldValue = $previousTemplateId === null ? null : (int) $previousTemplateId;
                $newValue = in_array($id, $ids, true) ? (int) $template->id : null;

                if ($oldValue === $newValue) {
                    continue;
                }

                if ($type = DocumentType::find($id)) {
                    $this->auditEvent(
                        'template_assigned',
                        $type,
                        "Updated template assignment for {$type->name}",
                        ['document_template_id' => $oldValue],
                        ['document_template_id' => $newValue],
                    );
                }
            }
        });
    }
}
