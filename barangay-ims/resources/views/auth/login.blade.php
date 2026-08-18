@extends('layouts.app')

@section('title', 'Login')

@push('styles')
<style>
    .login-bg {
        background-image: url('/images/barangay.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center login-bg relative p-4">
    <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/50 to-brand-900/60"></div>
    <div class="w-full max-w-md relative animate-fade-in">
        <div class="bg-white rounded-2xl shadow-2xl p-8 animate-slide-up">
            <div class="text-center mb-6">
                <img src="/images/logo.png" alt="Barangay Logo" class="w-16 h-16 mx-auto mb-3 object-contain">
                <h1 class="text-xl font-bold text-gray-900">Barangay Concepcion</h1>
                <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
            </div>
            @if(session('success'))
                <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-start gap-3" role="status">
                    <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium">Login failed</p>
                        <ul class="text-sm mt-1 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-5">
                    <x-input-field name="email" label="Email" type="email" :required="true" placeholder="you@example.com" value="{{ old('email') }}" autocomplete="email" class="[&_input]:rounded-xl" autofocus />
                </div>

                <div class="mb-5">
                    <x-input-field name="password" label="Password" type="password" :required="true" placeholder="Enter your password" autocomplete="current-password" class="[&_input]:rounded-xl" />
                </div>

                <div class="flex items-center mb-6">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 focus:ring-2 group-focus-within:ring-2 group-focus-within:ring-brand-200 group-focus-within:rounded">
                        <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Remember me</span>
                    </label>
                </div>

                <button type="submit" data-loading-text="Signing In..." class="w-full bg-brand-800 text-white py-2.5 rounded-xl font-medium hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all duration-150 active:scale-[0.98]">
                    Sign In
                </button>

                <div class="mt-4 text-center">
                    <span class="text-sm text-gray-500">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-sm text-brand-600 hover:text-brand-800 font-medium ml-1">Create one</a>
                </div>

            </form>
        </div>

        <p class="text-center mt-6 text-sm text-brand-300 animate-fade-in">
            &copy; {{ date('Y') }} Barangay Concepcion Information Management System
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var firstError = document.querySelector('[aria-invalid="true"]');
        if (firstError) {
            firstError.focus();
            var wrapper = firstError.closest('.mb-5');
            if (wrapper) wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
@endpush
