@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 mb-6">
    <x-stat-card gradient="from-blue-500 to-blue-700" :value="$totalResidents" label="Total Residents" :sublabel="'+' . $todayResidents . ' today'" :icon="'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'" :href="route('residents.index')" />
    <x-stat-card gradient="from-emerald-500 to-emerald-700" :value="$totalHouseholds" label="Households" :sublabel="$avgHouseholdSize . ' avg'" :icon="'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'" :href="route('households.index')" />
    <x-stat-card gradient="from-amber-500 to-amber-700" :value="$totalBlotters" label="Blotter Records" :sublabel="$blotterResolvedRate . '% resolved'" :icon="'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'" :href="route('blotters.index')" />
    <x-stat-card gradient="from-rose-500 to-rose-700" :value="$pendingBlotters" label="Pending Blotters" :sublabel="'needs action'" :icon="'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'" :href="route('blotters.index')" />
    <x-stat-card gradient="from-indigo-500 to-indigo-700" :value="$totalDocuments" label="Total Documents" :sublabel="'+' . $todayDocuments . ' today'" :icon="'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'" :href="route('documents.index')" />
    <x-stat-card gradient="from-orange-500 to-orange-700" :value="$pendingDocuments" label="Pending Documents" :sublabel="'needs action'" :icon="'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'" :href="route('documents.index')" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.857 0a6 6 0 00-12 0"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900">{{ $voterCount }}</div>
                <div class="text-xs text-gray-500 font-medium">Registered Voters</div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900">{{ $seniorCount }}</div>
                <div class="text-xs text-gray-500 font-medium">Senior Citizens</div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900">{{ $pwdCount }}</div>
                <div class="text-xs text-gray-500 font-medium">PWD</div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900">{{ $fourPsCount }}</div>
                <div class="text-xs text-gray-500 font-medium">4Ps Members</div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Monthly Trends (Last 6 Months)</h3>
        </div>
        <div class="p-5">
            @php
                $maxTrend = max(
                    $residentsByMonth->max('total') ?? 0,
                    $documentsByMonth->max('total') ?? 0,
                    $blottersByMonth->max('total') ?? 0,
                    1
                );

                $monthLabels = $residentsByMonth->pluck('label');
                $residentsByMonth = $residentsByMonth->keyBy('label');
                $documentsByMonth = $documentsByMonth->keyBy('label');
                $blottersByMonth = $blottersByMonth->keyBy('label');
            @endphp
            @if($residentsByMonth->count() > 0 || $documentsByMonth->count() > 0 || $blottersByMonth->count() > 0)
                <div class="space-y-4">
                    @foreach($monthLabels as $label)
                        @php
                            $docTotal = $documentsByMonth[$label]->total ?? 0;
                            $blotTotal = $blottersByMonth[$label]->total ?? 0;
                            $resTotal = $residentsByMonth[$label]->total ?? 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span class="font-medium text-gray-700">{{ $label }}</span>
                                <span>R:{{ $resTotal }} D:{{ $docTotal }} B:{{ $blotTotal }}</span>
                            </div>
                            <div class="flex gap-1 items-center h-5">
                                <div class="h-4 bg-blue-500 rounded-l" style="width: {{ ($resTotal / $maxTrend) * 100 }}%"></div>
                                <div class="h-4 bg-indigo-400" style="width: {{ ($docTotal / $maxTrend) * 100 }}%"></div>
                                <div class="h-4 bg-amber-400 rounded-r" style="width: {{ ($blotTotal / $maxTrend) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    <div class="flex gap-4 text-xs text-gray-500 pt-2 border-t border-gray-100">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-blue-500"></span> Residents</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-indigo-400"></span> Documents</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-amber-400"></span> Blotters</span>
                    </div>
                </div>
            @else
                <p class="text-gray-400 text-sm text-center py-4">No trend data available yet.</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Gender Distribution</h3>
        </div>
        <div class="p-5">
            @php
                $maleCount = $genderDistribution['male'] ?? 0;
                $femaleCount = $genderDistribution['female'] ?? 0;
                $otherCount = $genderDistribution['other'] ?? 0;
                $totalGender = $maleCount + $femaleCount + $otherCount;
                $malePct = $totalGender > 0 ? round(($maleCount / $totalGender) * 100) : 0;
                $femalePct = $totalGender > 0 ? round(($femaleCount / $totalGender) * 100) : 0;
            @endphp
            @if($totalGender > 0)
                <div class="flex justify-center mb-5">
                    <div class="relative w-32 h-32">
                        <svg class="w-32 h-32 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="2.8"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#3b82f6" stroke-width="2.8"
                                stroke-dasharray="{{ $malePct }} {{ 100 - $malePct }}"
                                stroke-dashoffset="0" stroke-linecap="round"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#ec4899" stroke-width="2.8"
                                stroke-dasharray="{{ $femalePct }} {{ 100 - $femalePct }}"
                                stroke-dashoffset="{{ -$malePct }}" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-900">{{ $totalGender }}</span>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Male</span>
                        <span class="font-semibold text-gray-900">{{ $maleCount }} ({{ $malePct }}%)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> Female</span>
                        <span class="font-semibold text-gray-900">{{ $femaleCount }} ({{ $femalePct }}%)</span>
                    </div>
                    @if($otherCount > 0)
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span> Other</span>
                        <span class="font-semibold text-gray-900">{{ $otherCount }}</span>
                    </div>
                    @endif
                </div>
            @else
                <p class="text-gray-400 text-sm text-center py-8">No data available.</p>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Residents by Purok</h3>
            <a href="{{ route('reports.residents') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                View Report &rarr;
            </a>
        </div>
        <div class="p-5 space-y-2">
            @if($residentByPurok->count() > 0)
                @php $maxTotal = $residentByPurok->max('total'); @endphp
                @foreach($residentByPurok as $item)
                    <div>
                        <div class="flex justify-between items-center text-sm mb-1">
                            <span class="text-gray-700">{{ $item->purok ?? 'Unassigned' }}</span>
                            <span class="font-semibold text-gray-900">{{ $item->total }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-gradient-to-r from-brand-500 to-brand-600 h-2 rounded-full transition-all" style="width: {{ $maxTotal > 0 ? ($item->total / $maxTotal) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-400 text-sm text-center py-4">No data available.</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Blotter Records by Status</h3>
            <a href="{{ route('reports.blotters') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                View Report &rarr;
            </a>
        </div>
        <div class="p-5 space-y-3">
            @if($blotterByStatus->count() > 0)
                @php
                    $statusColors = [
                        'pending' => 'bg-amber-500',
                        'hearing' => 'bg-blue-500',
                        'resolved' => 'bg-emerald-500',
                        'dismissed' => 'bg-gray-400',
                    ];
                    $statusBgColors = [
                        'pending' => 'text-amber-700 bg-amber-50 border-amber-200',
                        'hearing' => 'text-blue-700 bg-blue-50 border-blue-200',
                        'resolved' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                        'dismissed' => 'text-gray-600 bg-gray-50 border-gray-200',
                    ];
                @endphp
                @foreach($blotterByStatus as $item)
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $statusColors[$item->status] ?? 'bg-gray-300' }}"></span>
                        <span class="flex-1 text-sm text-gray-700 capitalize">{{ $item->status }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $item->total }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full border {{ $statusBgColors[$item->status] ?? 'text-gray-500 bg-gray-50' }}">
                            {{ $item->status }}
                        </span>
                    </div>
                @endforeach
            @else
                <p class="text-gray-400 text-sm text-center py-4">No data available.</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Document Requests by Status</h3>
            <a href="{{ route('reports.documents') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                View Report &rarr;
            </a>
        </div>
        <div class="p-5 space-y-3">
            @if($documentByStatus->count() > 0)
                @php
                    $docStatusColors = [
                        'pending' => 'bg-amber-500',
                        'approved' => 'bg-emerald-500',
                        'released' => 'bg-blue-500',
                        'cancelled' => 'bg-gray-400',
                    ];
                    $docStatusBgColors = [
                        'pending' => 'text-amber-700 bg-amber-50 border-amber-200',
                        'approved' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                        'released' => 'text-blue-700 bg-blue-50 border-blue-200',
                        'cancelled' => 'text-gray-600 bg-gray-50 border-gray-200',
                    ];
                @endphp
                @foreach($documentByStatus as $item)
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $docStatusColors[$item->status] ?? 'bg-gray-300' }}"></span>
                        <span class="flex-1 text-sm text-gray-700 capitalize">{{ $item->status }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $item->total }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full border {{ $docStatusBgColors[$item->status] ?? 'text-gray-500 bg-gray-50' }}">
                            {{ $item->status }}
                        </span>
                    </div>
                @endforeach
            @else
                <p class="text-gray-400 text-sm text-center py-4">No data available.</p>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Recent Residents</h3>
            <a href="{{ route('residents.index') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View All &rarr;</a>
        </div>
        <div class="p-4">
            @forelse($recentResidents as $resident)
                <a href="{{ route('residents.show', $resident) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">
                        {{ substr($resident->full_name, 0, 2) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate group-hover:text-brand-700">{{ $resident->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $resident->purok ?? 'No purok' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @empty
                <p class="text-gray-400 text-sm text-center py-4">No residents yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Recent Blotters</h3>
            <a href="{{ route('blotters.index') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View All &rarr;</a>
        </div>
        <div class="p-4">
            @forelse($recentBlotters as $blotter)
                <a href="{{ route('blotters.show', $blotter) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0">
                        B
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate group-hover:text-brand-700">{{ $blotter->blotter_number }}</p>
                        <p class="text-xs text-gray-400">{{ $blotter->incident_type }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @empty
                <p class="text-gray-400 text-sm text-center py-4">No blotters yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Recent Documents</h3>
            <a href="{{ route('documents.index') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View All &rarr;</a>
        </div>
        <div class="p-4">
            @forelse($recentDocuments as $document)
                <a href="{{ route('documents.show', $document) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                        D
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate group-hover:text-brand-700">{{ $document->control_number }}</p>
                        <p class="text-xs text-gray-400">{{ $document->documentType->name ?? 'N/A' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @empty
                <p class="text-gray-400 text-sm text-center py-4">No documents yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection