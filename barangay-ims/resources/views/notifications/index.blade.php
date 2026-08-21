@extends('layouts.app')
@section('title', 'Notifications')
@section('header', 'Notifications')
@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b flex flex-wrap items-center justify-between gap-3">
        <h3 class="font-semibold text-gray-900">Operational reminders</h3>
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="min-h-11 px-3 text-sm font-medium text-brand-700 hover:underline">Mark all as read</button></form>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($notifications as $notification)
        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="p-4 sm:p-5 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/50' }}">@csrf @method('PATCH')
            <button class="min-h-11 w-full text-left break-anywhere"><span class="block font-medium text-gray-900">{{ $notification->data['title'] ?? 'Reminder' }}</span><span class="block mt-1 text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</span><span class="block mt-2 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span></button>
        </form>
        @empty <p class="p-10 text-center text-sm text-gray-500">No notifications yet.</p> @endforelse
    </div>
    @if($notifications->hasPages())<div class="p-4 border-t">{{ $notifications->links() }}</div>@endif
</div>
@endsection
