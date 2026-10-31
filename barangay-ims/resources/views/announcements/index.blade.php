@extends('layouts.app')

@section('title', 'Announcements')
@section('header', 'Announcements')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-4 md:p-5 border-b border-gray-100 flex flex-wrap gap-3 justify-between items-center">
        <form method="GET" action="{{ route('announcements.index') }}" class="flex flex-wrap gap-2 items-center">
            <select name="status" aria-label="Filter by status"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="">All statuses</option>
                @foreach(\App\Models\Announcement::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="category" aria-label="Filter by category"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="">All categories</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Filter</button>
        </form>
        @can('create', \App\Models\Announcement::class)
        <a href="{{ route('announcements.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Announcement
        </a>
        @endcan
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($announcements as $announcement)
            <div class="p-4 sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('announcements.show', $announcement) }}" class="font-semibold text-gray-900 hover:text-brand-700 break-anywhere">
                                {{ $announcement->title }}
                            </a>
                            <x-status-badge :status="$announcement->status" size="sm">{{ ucfirst($announcement->status) }}</x-status-badge>
                            <span class="text-xs px-2 py-0.5 rounded-full border bg-gray-50 border-gray-200 text-gray-600">{{ $categories[$announcement->category] ?? ucfirst($announcement->category) }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 break-anywhere">{{ \Illuminate\Support\Str::limit($announcement->body, 140) }}</p>
                        <p class="mt-1.5 text-xs text-gray-400">
                            {{ $announcement->author?->name ?? 'Unknown' }}
                            @if($announcement->published_at)
                                &middot; Published {{ $announcement->published_at->format('M d, Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        @can('update', $announcement)
                        <a href="{{ route('announcements.edit', $announcement) }}" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" aria-label="Edit announcement {{ $announcement->title }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endcan
                        @if($announcement->isDraft() || $announcement->isArchived())
                            @can('publish', $announcement)
                            <form method="POST" action="{{ route('announcements.publish', $announcement) }}">@csrf @method('PATCH')
                                <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" aria-label="Publish announcement {{ $announcement->title }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            @endcan
                        @endif
                        @if($announcement->isPublished())
                            @can('unpublish', $announcement)
                            <form method="POST" action="{{ route('announcements.unpublish', $announcement) }}">@csrf @method('PATCH')
                                <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" aria-label="Archive announcement {{ $announcement->title }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                </button>
                            </form>
                            @endcan
                        @endif
                        @can('delete', $announcement)
                        <button type="button" onclick="openModal_deleteModal('{{ route('announcements.destroy', $announcement) }}')" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Delete announcement {{ $announcement->title }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state icon="M11 5.882V19.24a1.76 1.76 0 01-3.177.918l-2.111-2.423A2.25 2.25 0 014 16.72V7.28a2.25 2.25 0 01.918-1.82L7.318 3.06A1.76 1.76 0 019.5 2.061c.918.14 1.5.884 1.5 1.82zM19.5 6c0-1.105-.895-2-2-2s-2 .895-2 2v11a2 2 0 002 2h2v-3.5a3.5 3.5 0 00-3.5-3.5H15V6h4.5z" title="No announcements found" message="Create an announcement to keep the community informed." />
        @endforelse
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $announcements->links() }}</div>
</div>
@endsection
