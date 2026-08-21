<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\DocumentTemplate;
use Illuminate\Support\Str;

class DocumentTemplateRenderer
{
    public const PLACEHOLDERS = [
        'resident_name', 'first_name', 'middle_name', 'last_name', 'age', 'birthdate', 'civil_status',
        'sex', 'address', 'household', 'document_type', 'purpose', 'control_number', 'request_date',
        'issue_date', 'processing_days', 'fee', 'barangay_name', 'municipality', 'province',
        'signatory_name', 'signatory_position',
    ];

    public function templateFor(DocumentRequest $request): DocumentTemplate
    {
        return $request->documentType?->documentTemplate?->is_active
            ? $request->documentType->documentTemplate
            : DocumentTemplate::where('is_default', true)->where('is_active', true)->firstOrFail();
    }

    public function render(DocumentRequest $request, ?DocumentTemplate $template = null, bool $useIssuedSnapshot = true): array
    {
        if ($useIssuedSnapshot && $request->status === 'released' && $request->issued_document_snapshot) {
            return $request->issued_document_snapshot;
        }
        $request->loadMissing(['resident.household', 'documentType.documentTemplate']);
        $template ??= $this->templateFor($request);
        $resident = $request->resident;
        $values = [
            'resident_name' => $resident?->full_name, 'first_name' => $resident?->first_name,
            'middle_name' => $resident?->middle_name, 'last_name' => $resident?->last_name,
            'age' => $resident?->birth_date?->age, 'birthdate' => $resident?->birth_date?->format('F j, Y'),
            'civil_status' => $resident?->civil_status ? Str::title($resident->civil_status) : null,
            'sex' => $resident?->gender ? Str::title($resident->gender) : null,
            'address' => collect([$resident?->street_address, $resident?->purok])->filter()->implode(', '),
            'household' => $resident?->household?->household_number,
            'document_type' => $request->documentType?->name, 'purpose' => $request->purpose ?: 'a lawful purpose',
            'control_number' => $request->control_number, 'request_date' => $request->created_at?->format('F j, Y'),
            'issue_date' => ($request->released_date ?? now())->format('F j, Y'),
            'processing_days' => $request->documentType?->processing_days,
            'fee' => 'PHP '.number_format((float) $request->fee_amount, 2),
            'barangay_name' => $template->barangay_name, 'municipality' => $template->municipality,
            'province' => $template->province, 'signatory_name' => $template->signatory_name,
            'signatory_position' => $template->signatory_position,
        ];
        $fields = ['title', 'header_line_1', 'header_line_2', 'header_line_3', 'office_name', 'opening_phrase', 'body', 'closing_text', 'footer_text'];
        $rendered = [];
        foreach ($fields as $field) $rendered[$field] = $this->substitute((string) $template->{$field}, $values);
        return $rendered + [
            'template_id' => $template->id, 'template_name' => $template->name,
            'signatory_name' => e($template->signatory_name ?: ''),
            'signatory_position' => e($template->signatory_position ?: 'Punong Barangay'),
            'logo_path' => $template->logo_path, 'resident_photo_path' => $resident?->photo_path,
            'options' => collect(['show_control_number','show_fee','show_issue_date','show_resident_photo','show_logo'])
                ->mapWithKeys(fn ($key) => [$key => (bool) $template->{$key}])->all(),
            'meta' => ['control_number' => e($request->control_number), 'fee' => e($values['fee']), 'issue_date' => e($values['issue_date'])],
            'captured_at' => now()->toIso8601String(),
        ];
    }

    public function snapshot(DocumentRequest $request): array { return $this->render($request, null, false); }

    public function substitute(string $text, array $values): string
    {
        $escapedTemplate = e($text);
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($match) use ($values) {
            $key = $match[1];
            return array_key_exists($key, $values) ? e((string) ($values[$key] ?? '')) : e("[UNKNOWN: {$key}]");
        }, $escapedTemplate) ?? '';
    }
}
