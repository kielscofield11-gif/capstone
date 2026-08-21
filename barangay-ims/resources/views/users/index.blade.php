@extends('layouts.app')

@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-4 md:p-5 border-b border-gray-100 flex flex-wrap gap-3 justify-between items-center">
        <p class="text-sm text-gray-500">Manage system user accounts</p>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New User
        </a>
    </div>

    <div class="responsive-table" tabindex="0" role="region" aria-label="Users table">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Created</th>
                    <th class="text-right px-4 md:px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 md:px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">{{ substr($user->name, 0, 2) }}</div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400 sm:hidden">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $user->email }}</td>
                        <td class="px-4 md:px-5 py-3">
                            <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full font-medium capitalize {{ $user->role == 'admin' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($user->role == 'secretary' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($user->role == 'kagawad' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-50 text-gray-700 border border-gray-200')) }}">
                                {{ $user->role_label }}
                            </span>
                        </td>
                        <td class="px-4 md:px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 md:px-5 py-3 text-gray-600 hidden md:table-cell">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-4 md:px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('users.edit', $user) }}" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" aria-label="Edit user {{ $user->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if(auth()->id() !== $user->id)
                                    <button type="button" onclick="openModal_deleteModal('{{ route('users.destroy', $user) }}')" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Delete user {{ $user->name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-state icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" title="No users found" message="No user accounts have been created yet." :colspan="6" />
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $users->links() }}</div>
</div>
@endsection
