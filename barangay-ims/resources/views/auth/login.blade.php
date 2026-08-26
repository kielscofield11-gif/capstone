@extends('layouts.app')

@section('title', 'Login')

@push('styles')
<style>
    /* Keep the authentication screen usable when the Tailwind CDN is unavailable. */
    .login-page, .login-page * { box-sizing: border-box; }
    .login-page { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; overflow-x: hidden; font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; }
    .login-bg {
        background-color: #6b8eaf;
        background-image: url('/images/barangay-bg.png?v=2');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .login-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0, 0, 0, .42), rgba(15, 31, 48, .48)); }
    .login-shell { position: relative; z-index: 1; width: 100%; max-width: 28rem; }
    .login-card { background: #fff; border-radius: 1rem; padding: 2rem; box-shadow: 0 1.5rem 3rem rgba(0, 0, 0, .28); }
    .login-logo { display: block; width: 4rem; height: 4rem; margin: 0 auto .75rem; object-fit: contain; }
    .login-heading { margin: 0; color: #111827; text-align: center; font-size: 1.25rem; font-weight: 700; }
    .login-subheading { margin: .25rem 0 1.5rem; color: #6b7280; text-align: center; font-size: .875rem; }
    .login-card form > .mb-5 { margin-bottom: 1.25rem; }
    .login-card label { display: block; margin-bottom: .25rem; color: #374151; font-size: .875rem; font-weight: 500; }
    .login-card input:not([type="checkbox"]) { display: block; width: 100%; min-height: 2.75rem; padding: .625rem .75rem; border: 1px solid #d1d5db; border-radius: .75rem; background: #fff; color: #111827; font: inherit; font-size: .875rem; }
    .login-card input:not([type="checkbox"]):focus { outline: 2px solid #93c5fd; outline-offset: 1px; border-color: #2563eb; }
    .login-card .relative { position: relative; }
    .login-card .relative button { position: absolute; right: .25rem; top: 50%; transform: translateY(-50%); padding: .5rem; border: 0; background: transparent; color: #9ca3af; cursor: pointer; }
    .login-card .relative button:hover { color: #4b5563; }
    .login-card input[type="checkbox"] { width: 1rem; height: 1rem; accent-color: #1e3a5f; }
    .login-card form > .flex { display: flex; align-items: center; margin-bottom: 1.5rem; }
    .login-card form > .flex label { display: flex; align-items: center; gap: .5rem; margin: 0; cursor: pointer; }
    .login-card button[type="submit"] { width: 100%; min-height: 2.75rem; border: 0; border-radius: .75rem; background: #1e3a5f; color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
    .login-card button[type="submit"]:hover { background: #172e46; }
    .login-card form > div:last-child { margin-top: 1rem; text-align: center; color: #6b7280; font-size: .875rem; }
    .login-card form > div:last-child a { color: #2563eb; font-weight: 600; text-decoration: none; }
    .login-card form > div:last-child a:hover { color: #1e3a5f; text-decoration: underline; }
    .login-footer { margin-top: 1.5rem; color: #bfdbfe; text-align: center; font-size: .875rem; }
    @media (max-width: 480px) { .login-page { align-items: flex-start; padding: 1rem .75rem; } .login-card { padding: 1.5rem 1.25rem; } }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center login-bg relative p-4">
    <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/50 to-brand-900/60"></div>
    <div class="w-full max-w-md relative animate-fade-in">
        <div class="bg-white rounded-2xl shadow-2xl p-8 animate-slide-up">
            <div class="text-center mb-6">
                <img src="/images/logo.png" alt="Barangay Logo" class="w-16 h-16 mx-auto mb-3 object-contain login-logo">
                <h1 class="text-xl font-bold text-gray-900 login-heading">Barangay Concepcion</h1>
                <p class="text-sm text-gray-500 mt-1 login-subheading">Sign in to your account</p>
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

        <p class="text-center mt-6 text-sm text-brand-300 animate-fade-in login-footer">
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
