@extends('layouts.app')

@section('title', 'Add User')
@section('header', 'Add User')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 max-w-lg mx-auto">
    <form method="POST" action="{{ route('users.store') }}" class="p-6 md:p-8 space-y-5">
        @csrf
        <x-input-field name="name" label="Name" :required="true" placeholder="John Doe" value="{{ old('name') }}" />
        <x-input-field name="email" label="Email" type="email" :required="true" placeholder="john@example.com" value="{{ old('email') }}" />
        <x-input-field name="password" label="Password" type="password" :required="true" />
        <p class="text-xs text-gray-500 -mt-3">At least 10 characters with upper and lower case letters, a number, and a symbol.</p>
        <x-input-field name="password_confirmation" label="Confirm Password" type="password" :required="true" />
        <x-input-field name="role" label="Role" type="select" :required="true" value="{{ old('role', 'staff') }}" :options="['staff' => 'Staff', 'kagawad' => 'Kagawad', 'secretary' => 'Secretary', 'captain' => 'Barangay Captain', 'admin' => 'Administrator']" />
        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 border-t border-gray-100 [&>*]:w-full sm:[&>*]:w-auto [&>*]:min-h-11">
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-brand-800 text-white rounded-lg hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all">Create User</button>
        </div>
    </form>
</div>
@endsection
