@extends('layouts.app')

@section('title', 'Blotter Reports')
@section('header', 'Blotter Reports')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="p-4 md:p-5 border-b border-gray-100">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="hearing" {{ request('status') == 'hearing' ? 'selected' : '' }}>Hearing</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>
            <div>
                <select name="incident_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Types</option>
                    @foreach($incidentTypes as $type)
                        <option value="{{ $type }}" {{ request('incident_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <button type="submit" class="bg-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Generate</button>
            <a href="{{ route('reports.blotters') }}" class="text-sm text-gray-500 hover:text-gray-700 px-2 py-2">Reset</a>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="text-center mb-4">
            <div class="text-3xl font-bold text-brand-700">{{ $totalCount }}</div>
            <div class="text-xs text-gray-500">Total Records</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">By Status</h3>
        <div class="space-y-2">
            @foreach($statusBreakdown as $status => $count)
                <div class="flex justify-between items-center text-sm">
                    <span class="capitalize text-gray-700">{{ $status }}</span>
                    <span class="font-semibold text-gray-900">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">By Incident Type</h3>
        <div class="space-y-2">
            @foreach($typeBreakdown as $type => $count)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-700">{{ $type }}</span>
                    <span class="font-semibold text-gray-900">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="font-semibold text-gray-900 mb-4">Monthly Trend</h3>
    @php $maxMonthly = $maxBlotterMonthlyCount; @endphp
    <div class="space-y-2">
        @foreach($monthlyBreakdown as $month => $count)
            <div>
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="text-gray-700">{{ $month }}</span>
                    <span class="font-semibold text-gray-900">{{ $count }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($count / $maxMonthly) * 100 }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <h3 class="font-semibold text-gray-900">Blotter List ({{ $totalCount }})</h3>
        <div class="grid grid-cols-1 min-[390px]:grid-cols-3 sm:flex gap-2 w-full sm:w-auto [&>*]:justify-center [&>*]:min-h-11">
            <a href="{{ route('exports.blotters.pdf', request()->query()) }}" class="inline-flex items-center gap-1.5 text-sm bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>
            <a href="{{ route('exports.blotters.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 text-sm bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-emerald-700">Export Excel</a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-sm bg-gray-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
        </div>
    </div>
    <div class="responsive-table" tabindex="0" role="region" aria-label="Blotter report table">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Blotter #</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Complainant</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Respondent</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($blotters as $b)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $b->blotter_number }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $b->complainant->full_name ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $b->respondent->full_name ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $b->incident_type }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $b->incident_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <x-status-badge :status="$b->status">{{ ucfirst($b->status) }}</x-status-badge>
                        </td>
                    </tr>
                @empty
                    <x-empty-state icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" title="No blotter records found" message="No blotter records match your filters." :colspan="6" />
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
