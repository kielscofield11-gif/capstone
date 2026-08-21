@extends('layouts.app')

@section('title', 'Create Account')

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
        <div class="bg-white rounded-2xl shadow-2xl p-5 sm:p-8 animate-slide-up">
            <div class="text-center mb-6">
                <img src="/images/logo.png" alt="Barangay Logo" class="w-16 h-16 mx-auto mb-3 object-contain">
                <h1 class="text-xl font-bold text-gray-900">Create Account</h1>
                <p class="text-sm text-gray-500 mt-1">Register a new account</p>
            </div>
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
                        <p class="text-sm font-medium">Registration failed</p>
                        <ul class="text-sm mt-1 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <div class="mb-5">
                    <x-input-field name="name" label="Full Name" type="text" :required="true" placeholder="Juan Dela Cruz" value="{{ old('name') }}" autocomplete="name" class="[&_input]:rounded-xl" autofocus />
                </div>

                <div class="mb-5">
                    <x-input-field name="email" label="Email" type="email" :required="true" placeholder="you@example.com" value="{{ old('email') }}" autocomplete="email" class="[&_input]:rounded-xl" />
                </div>

                <div class="mb-5">
                    <x-input-field name="password" label="Password" type="password" :required="true" placeholder="Enter a password" autocomplete="new-password" class="[&_input]:rounded-xl" />
                    <p class="text-xs text-gray-500 mt-1.5">Use at least 10 characters with upper and lower case letters, a number, and a symbol.</p>
                </div>

                <div class="mb-5">
                    <x-input-field name="password_confirmation" label="Confirm Password" type="password" :required="true" placeholder="Repeat password" autocomplete="new-password" class="[&_input]:rounded-xl" />
                </div>

                <button type="submit" data-loading-text="Creating Account..." class="w-full bg-brand-800 text-white py-2.5 rounded-xl font-medium hover:bg-brand-900 focus:ring-4 focus:ring-brand-200 transition-all duration-150 active:scale-[0.98]">
                    Create Account
                </button>

                <div class="mt-4 text-center">
                    <span class="text-sm text-gray-500">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-sm text-brand-600 hover:text-brand-800 font-medium ml-1">Sign in</a>
                </div>
            </form>
        </div>

        <p class="text-center mt-6 text-sm text-brand-300 animate-fade-in">
            &copy; {{ date('Y') }} Barangay Concepcion Information Management System
        </p>
    </div>
</div>
@endsection
