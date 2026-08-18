@extends('layouts.app')

@section('title', 'Audit Logs')
@section('header', 'Audit Logs')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="p-4 md:p-5 border-b border-gray-100">
        <x-filter-bar :route="route('audit-logs.index')" search-placeholder="Search user or description..." :filters="[
            ['name' => 'action', 'placeholder' => 'All Actions', 'options' => $actions->mapWithKeys(fn($a) => [$a => ucfirst($a)])->toArray()],
            ['name' => 'model_type', 'placeholder' => 'All Modules', 'options' => $models->mapWithKeys(fn($m) => [$m => str_replace('App\Models\\', '', $m)])->toArray()],
        ]" />
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Time</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Module</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $log->created_at->format('M d, g:i A') }}</td>
                        <td class="px-5 py-3 text-gray-900">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-5 py-3">
                            <x-status-badge :status="$log->action">{{ ucfirst($log->action) }}</x-status-badge>
                        </td>
                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ str_replace('App\Models\\', '', $log->model_type) }}</td>
                        <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $log->description }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs hidden lg:table-cell font-mono">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <x-empty-state icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" title="No audit logs found" message="Audit log entries will appear here as actions are performed." :colspan="6" />
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
