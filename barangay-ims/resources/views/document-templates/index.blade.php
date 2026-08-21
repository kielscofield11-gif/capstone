@extends('layouts.app')
@section('title','Document Templates')
@section('header','Document Templates')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="text-lg font-semibold">Printable document templates</h2><p class="text-sm text-amber-700">Temporary generic template. Replace or update this template when the approved official barangay document format becomes available.</p></div><a href="{{ route('document-templates.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-800 px-4 py-2 text-sm font-medium text-white">Add Template</a></div>
    <div class="responsive-table rounded-xl border bg-white" tabindex="0" role="region" aria-label="Document templates"><table class="min-w-full text-sm"><thead><tr class="border-b bg-gray-50 text-left"><th class="p-4">Name</th><th class="p-4">Status</th><th class="p-4">Assigned types</th><th class="p-4">Actions</th></tr></thead><tbody>@forelse($templates as $template)<tr class="border-b"><td class="p-4 font-medium">{{ $template->name }} @if($template->is_default)<span class="text-xs text-brand-700">(Fallback)</span>@endif</td><td class="p-4">{{ $template->is_active?'Active':'Inactive' }}</td><td class="p-4">{{ $template->document_types_count }}</td><td class="p-4"><a class="text-brand-700 underline" href="{{ route('document-templates.edit',$template) }}">Edit</a></td></tr>@empty<tr><td class="p-6 text-center text-gray-500" colspan="4">No templates found.</td></tr>@endforelse</tbody></table></div>{{ $templates->links() }}
</div>
@endsection
