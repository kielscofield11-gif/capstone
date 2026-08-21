@extends('layouts.app')

@section('title', 'Reports')
@section('header', 'Reports')

@section('content')
<div class="mb-5 flex justify-end"><a href="{{ route('exports.households.excel') }}" class="inline-flex text-sm bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-emerald-700">Export Households Excel</a></div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('reports.residents') }}" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-brand-200 transition-all">
        <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:bg-blue-100 transition-colors">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <h3 class="font-semibold text-gray-900 group-hover:text-brand-700 transition-colors">Resident Reports</h3>
        <p class="text-sm text-gray-500 mt-1">Demographics, population breakdown, and resident statistics</p>
    </a>

    <a href="{{ route('reports.blotters') }}" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-brand-200 transition-all">
        <div class="w-14 h-14 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:bg-amber-100 transition-colors">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="font-semibold text-gray-900 group-hover:text-brand-700 transition-colors">Blotter Reports</h3>
        <p class="text-sm text-gray-500 mt-1">Incident analysis, case status, and monthly trends</p>
    </a>

    <a href="{{ route('reports.documents') }}" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-brand-200 transition-all">
        <div class="w-14 h-14 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:bg-emerald-100 transition-colors">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="font-semibold text-gray-900 group-hover:text-brand-700 transition-colors">Document Reports</h3>
        <p class="text-sm text-gray-500 mt-1">Request volume, revenue, and document type analysis</p>
    </a>
</div>
@endsection
