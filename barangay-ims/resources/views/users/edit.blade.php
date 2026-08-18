@extends('layouts.app')

@section('title', 'Edit User')
@section('header', 'Edit User')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 md:p-8 space-y-5">
        @csrf @method('PUT')
        <x-input-field name="name" label="Name" :required="true" value="{{ old('name', $user->name) }}" />
        <x-input-field name="email" label="Email" type="email" :required="true" value="{{ old('email', $user->email) }}" />
        <x-input-field name="password" label="New Password" type="password" placeholder="Leave blank to keep current" />
        <x-input-field name="password_confirmation" label="Confirm Password" type="password" />
        <x-input-field name="role" label="Role" type="select" :required="true" value="{{ old('role', $user->role) }}" :options="['staff' => 'Staff', 'secretary' => 'Secretary', 'kagawad' => 'Kagawad', 'admin' => 'Admin']" />
        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
            </label>
            <span class="text-sm font-medium text-gray-700">Active</span>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Update User</button>
        </div>
    </form>
</div>
@endsection
