<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use App\Services\DocumentTemplateRenderer;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateController extends Controller
{
    use LogsAudit;

    public function index() { $this->authorize('viewAny', DocumentTemplate::class); return view('document-templates.index', ['templates' => DocumentTemplate::withCount('documentTypes')->latest()->paginate(15)]); }
    public function create() { $this->authorize('create', DocumentTemplate::class); return view('document-templates.form', ['template' => new DocumentTemplate, 'documentTypes' => DocumentType::orderBy('name')->get(), 'placeholders' => DocumentTemplateRenderer::PLACEHOLDERS]); }
    public function edit(DocumentTemplate $documentTemplate) { $this->authorize('update', $documentTemplate); return view('document-templates.form', ['template' => $documentTemplate->load('documentTypes'), 'documentTypes' => DocumentType::orderBy('name')->get(), 'placeholders' => DocumentTemplateRenderer::PLACEHOLDERS]); }

    public function store(Request $request) { $this->authorize('create', DocumentTemplate::class); $data=$this->validated($request); $types=$data['document_type_ids']??[]; unset($data['document_type_ids'],$data['logo']); $data['created_by']=$request->user()->id; if($request->hasFile('logo'))$data['logo_path']=$request->file('logo')->store('document-templates/logos','public'); $template=DocumentTemplate::create($data); $this->assign($template,$types); $this->auditCreated($template,"Created document template {$template->name}"); return redirect()->route('document-templates.edit',$template)->with('success','Template created.'); }
    public function update(Request $request, DocumentTemplate $documentTemplate) { $this->authorize('update',$documentTemplate); $data=$this->validated($request,$documentTemplate); if($documentTemplate->is_default)$data['is_active']=true; $types=$data['document_type_ids']??[]; unset($data['document_type_ids'],$data['logo']); $before=$this->auditSnapshot($documentTemplate); $oldLogo=$documentTemplate->logo_path; if($request->hasFile('logo'))$data['logo_path']=$request->file('logo')->store('document-templates/logos','public'); if($request->boolean('remove_logo'))$data['logo_path']=null; $documentTemplate->update($data); if($oldLogo && $oldLogo!==$documentTemplate->logo_path)Storage::disk('public')->delete($oldLogo); $this->assign($documentTemplate,$types); $this->auditUpdated($documentTemplate,$before,"Updated document template {$documentTemplate->name}",$documentTemplate->is_active?'updated':'deactivated'); return back()->with('success','Template updated.'); }
    public function destroy(DocumentTemplate $documentTemplate) { $this->authorize('delete',$documentTemplate); if($documentTemplate->is_default)return back()->with('error','The fallback template cannot be deleted.'); $before=$this->auditSnapshot($documentTemplate); $documentTemplate->delete(); $this->auditDeleted($documentTemplate,$before,"Deleted document template {$documentTemplate->name}"); return redirect()->route('document-templates.index')->with('success','Template deleted.'); }

    private function validated(Request $request, ?DocumentTemplate $template=null): array { $id=$template?->id; $data=$request->validate(['name'=>"required|string|max:255|unique:document_templates,name,$id",'title'=>'required|string|max:255','header_line_1'=>'nullable|string|max:255','header_line_2'=>'nullable|string|max:255','header_line_3'=>'nullable|string|max:255','office_name'=>'nullable|string|max:255','barangay_name'=>'nullable|string|max:255','municipality'=>'nullable|string|max:255','province'=>'nullable|string|max:255','opening_phrase'=>'nullable|string|max:2000','body'=>'required|string|max:20000','closing_text'=>'nullable|string|max:5000','signatory_name'=>'nullable|string|max:255','signatory_position'=>'nullable|string|max:255','footer_text'=>'nullable|string|max:5000','logo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048','document_type_ids'=>'array','document_type_ids.*'=>'integer|exists:document_types,id']); foreach(['show_control_number','show_fee','show_issue_date','show_resident_photo','show_logo','is_active'] as $key)$data[$key]=$request->boolean($key); return $data; }
    private function assign(DocumentTemplate $template,array $ids): void { $old=DocumentType::where('document_template_id',$template->id)->pluck('id')->all(); DocumentType::where('document_template_id',$template->id)->whereNotIn('id',$ids ?: [0])->update(['document_template_id'=>null]); DocumentType::whereIn('id',$ids)->update(['document_template_id'=>$template->id]); foreach(array_unique(array_merge($old,$ids)) as $id){if($type=DocumentType::find($id))$this->auditEvent('template_assigned',$type,"Updated template assignment for {$type->name}",['document_template_id'=>in_array($id,$old)?$template->id:null],['document_template_id'=>in_array($id,$ids)?$template->id:null]);} }
}
