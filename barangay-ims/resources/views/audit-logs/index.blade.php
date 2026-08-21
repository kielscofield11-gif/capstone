@extends('layouts.app')

@section('title', 'Audit Logs')
@section('header', 'Audit Logs')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="p-4 md:p-5 border-b border-gray-100">
        <x-filter-bar :route="route('audit-logs.index')" search-placeholder="User, record ID, or description..." :filters="[
            ['name' => 'user_id', 'placeholder' => 'All Users', 'options' => $users->toArray()],
            ['name' => 'action', 'placeholder' => 'All Actions', 'options' => $actions->mapWithKeys(fn($a) => [$a => str($a)->replace('_', ' ')->title()])->toArray()],
            ['name' => 'model_type', 'placeholder' => 'All Modules', 'options' => $models->mapWithKeys(fn($m) => [$m => class_basename($m)])->toArray()],
            ['name' => 'date_from', 'type' => 'date', 'placeholder' => 'From'],
            ['name' => 'date_to', 'type' => 'date', 'placeholder' => 'To'],
        ]" />
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($logs as $log)
            <details class="group p-4 md:p-5 hover:bg-gray-50/60 transition-colors">
                <summary class="cursor-pointer list-none grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 items-center">
                    <div class="md:col-span-2 text-sm text-gray-600 md:whitespace-nowrap">
                        <div class="font-medium text-gray-800">{{ $log->created_at->format('M d, Y') }}</div>
                        <div class="text-xs">{{ $log->created_at->format('g:i:s A') }}</div>
                    </div>
                    <div class="md:col-span-2 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $log->user?->name ?? 'System' }}</div>
                        <div class="text-xs text-gray-500">{{ $log->ip_address ?? 'No IP recorded' }}</div>
                    </div>
                    <div class="md:col-span-2 flex flex-wrap gap-2 items-center">
                        <x-status-badge :status="$log->action">{{ str($log->action)->replace('_', ' ')->title() }}</x-status-badge>
                        <span class="text-xs text-gray-500">{{ $log->module_label }} #{{ $log->model_id ?? '—' }}</span>
                    </div>
                    <div class="md:col-span-5 text-sm text-gray-700 break-anywhere">{{ $log->description }}</div>
                    <div class="md:col-span-1 text-right text-gray-400 group-open:rotate-180 transition-transform" aria-hidden="true">⌄</div>
                </summary>

                <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-1 lg:grid-cols-2 gap-4 min-w-0 break-anywhere">
                    @foreach(['Before' => $log->old_values, 'After' => $log->new_values] as $heading => $values)
                        <section class="rounded-lg border border-gray-200 overflow-hidden">
                            <h3 class="px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $heading === 'Before' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $heading }}</h3>
                            @if(is_array($values) && count($values))
                                <dl class="divide-y divide-gray-100">
                                    @foreach($values as $field => $value)
                                        <div class="grid grid-cols-5 gap-3 px-4 py-2 text-sm">
                                            <dt class="col-span-2 font-medium text-gray-600 break-words">{{ str($field)->replace('_', ' ')->title() }}</dt>
                                            <dd class="col-span-3 text-gray-900 break-words whitespace-pre-wrap">@if(is_bool($value)){{ $value ? 'Yes' : 'No' }}@elseif(is_array($value)){{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}@elseif($value === null)<span class="text-gray-400">None</span>@else{{ $value }}@endif</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="px-4 py-4 text-sm text-gray-400">No values recorded.</p>
                            @endif
                        </section>
                    @endforeach
                    @if($log->user_agent)
                        <p class="lg:col-span-2 text-xs text-gray-400 break-all">Device: {{ $log->user_agent }}</p>
                    @endif
                </div>
            </details>
        @empty
            <div class="p-8 text-center text-sm text-gray-500">No audit log entries match the selected filters.</div>
        @endforelse
    </div>

    @if($logs->hasPages())
        <div class="p-4 border-t border-gray-100">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
