@extends('layouts.app')

@section('title', 'Resident Reports')
@section('header', 'Resident Reports')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="p-4 md:p-5 border-b border-gray-100">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <select name="purok" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Puroks</option>
                    @foreach($puroks as $p)
                        <option value="{{ $p }}" {{ request('purok') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="gender" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Genders</option>
                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <input type="number" name="age_from" value="{{ request('age_from') }}" placeholder="Age from"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <input type="number" name="age_to" value="{{ request('age_to') }}" placeholder="Age to"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <button type="submit" class="bg-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Generate</button>
            <a href="{{ route('reports.residents') }}" class="text-sm text-gray-500 hover:text-gray-700 px-2 py-2">Reset</a>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Summary</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-brand-700">{{ $totalCount }}</div>
                <div class="text-xs text-gray-500">Total Residents</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $genderBreakdown['male'] }}</div>
                <div class="text-xs text-gray-500">Male</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-pink-600">{{ $genderBreakdown['female'] }}</div>
                <div class="text-xs text-gray-500">Female</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-amber-600">{{ $seniorCount }}</div>
                <div class="text-xs text-gray-500">Seniors</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $pwdCount }}</div>
                <div class="text-xs text-gray-500">PWDs</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $voterCount }}</div>
                <div class="text-xs text-gray-500">Voters</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center col-span-2">
                <div class="text-2xl font-bold text-purple-600">{{ $fourPsCount }}</div>
                <div class="text-xs text-gray-500">4Ps Beneficiaries</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Civil Status Breakdown</h3>
        @php $maxCivil = $maxCivilStatusCount; @endphp
        <div class="space-y-3">
            @foreach($civilStatusBreakdown as $status => $count)
                <div>
                    <div class="flex justify-between items-center text-sm mb-1">
                        <span class="capitalize text-gray-700">{{ $status }}</span>
                        <span class="font-semibold text-gray-900">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-brand-600 h-2 rounded-full" style="width: {{ ($count / $maxCivil) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="font-semibold text-gray-900 mb-4">Residents by Purok</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($purokBreakdown as $purok => $count)
            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2.5">
                <span class="text-sm text-gray-700">{{ $purok ?? 'Unassigned' }}</span>
                <span class="font-semibold text-gray-900">{{ $count }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-semibold text-gray-900">Resident List ({{ $totalCount }})</h3>
        <div class="flex gap-2">
            <a href="{{ route('exports.residents.pdf', request()->query()) }}" class="inline-flex items-center gap-1.5 text-sm bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-sm bg-gray-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Gender</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Age</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Purok</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Civil Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($residents as $r)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $r->full_name }}</td>
                        <td class="px-5 py-3 capitalize text-gray-600 hidden sm:table-cell">{{ $r->gender }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $r->age }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $r->purok ?? '-' }}</td>
                        <td class="px-5 py-3 capitalize text-gray-600 hidden sm:table-cell">{{ $r->civil_status }}</td>
                    </tr>
                @empty
                    <x-empty-state icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" title="No residents found" message="No residents match your filters." :colspan="5" />
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
