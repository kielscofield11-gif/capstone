@extends('layouts.app')

@section('title', 'Edit Announcement')
@section('header', 'Edit Announcement')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-2xl mx-auto">
    <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="p-6 md:p-8 space-y-5">
        @csrf
        @method('PUT')
        <x-input-field name="title" label="Title" :required="true" value="{{ old('title', $announcement->title) }}" />
        <x-input-field name="body" label="Body" type="textarea" :required="true" value="{{ old('body', $announcement->body) }}" />
        <x-input-field name="category" label="Category" type="select" :required="true" value="{{ old('category', $announcement->category) }}" :options="$categories" />

        @if(($announcement->isDraft() || $announcement->isArchived()) && auth()->user()->can('publish', $announcement))
        <div class="flex items-start gap-3 p-4 rounded-lg bg-gray-50 border border-gray-200">
            <input type="checkbox" name="action_publish" value="1" {{ old('action_publish') ? 'checked' : '' }}
                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            <div>
                <label for="action_publish" class="text-sm font-medium text-gray-700">Publish immediately</label>
                <p class="text-xs text-gray-500">Unchecked announcements stay as drafts.</p>
            </div>
        </div>
        @endif

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 border-t border-gray-100 [&>*]:w-full sm:[&>*]:w-auto [&>*]:min-h-11">
            <a href="{{ route('announcements.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Save</button>
        </div>
    </form>
</div>
@endsection
