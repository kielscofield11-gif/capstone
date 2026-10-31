@extends('layouts.app')

@section('title', $announcement->title)
@section('header', 'Announcement')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-status-badge :status="$announcement->status" size="base">{{ ucfirst($announcement->status) }}</x-status-badge>
                <span class="text-xs px-2 py-0.5 rounded-full border bg-gray-50 border-gray-200 text-gray-600">{{ $categories[$announcement->category] ?? ucfirst($announcement->category) }}</span>
            </div>
            <div class="flex items-center gap-1">
                @can('update', $announcement)
                <a href="{{ route('announcements.edit', $announcement) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-brand-700 hover:bg-brand-50 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @endcan
                @if($announcement->isDraft() || $announcement->isArchived())
                    @can('publish', $announcement)
                    <form method="POST" action="{{ route('announcements.publish', $announcement) }}">@csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Publish
                        </button>
                    </form>
                    @endcan
                @elseif($announcement->isPublished())
                    @can('unpublish', $announcement)
                    <form method="POST" action="{{ route('announcements.unpublish', $announcement) }}">@csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            Archive
                        </button>
                    </form>
                    @endcan
                @endif
                @can('delete', $announcement)
                <button type="button" onclick="openModal_deleteModal('{{ route('announcements.destroy', $announcement) }}')" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
                @endcan
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 break-anywhere">{{ $announcement->title }}</h3>
            <p class="mt-1 text-xs text-gray-400">
                Posted by {{ $announcement->author?->name ?? 'Unknown' }}
                @if($announcement->published_at)
                    &middot; Published {{ $announcement->published_at->format('M d, Y h:i A') }}
                @endif
                &middot; Updated {{ $announcement->updated_at->format('M d, Y h:i A') }}
            </p>
            <div class="mt-4 text-sm text-gray-800 whitespace-pre-wrap break-anywhere">{{ $announcement->body }}</div>
        </div>
    </div>

    <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-brand-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to announcements
    </a>
</div>
@endsection
