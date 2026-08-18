@props(['name' => '', 'label' => '', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => '', 'options' => [], 'class' => '', 'autocomplete' => ''])

@php
$errorKey = str_replace(['[', ']'], ['.', ''], $name);
$hasError = $errors->has($errorKey);
$errorClass = $hasError ? 'border-red-400 ring-2 ring-red-200' : 'border-gray-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500';
$ariaInvalid = $hasError ? 'true' : 'false';
@endphp

<div class="{{ $class }}">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    @if($type === 'select')
        <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes }}
            class="w-full border rounded-lg px-3 py-2.5 text-sm transition-colors {{ $errorClass }}"
            @if($hasError) aria-invalid="{{ $ariaInvalid }}" aria-describedby="error-{{ $name }}" @endif>
            @foreach($options as $key => $optLabel)
                <option value="{{ $key }}" {{ old(str_replace(['[', ']'], ['.', ''], $name), $value) == $key ? 'selected' : '' }}>{{ $optLabel }}</option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes }}
            placeholder="{{ $placeholder }}"
            class="w-full border rounded-lg px-3 py-2.5 text-sm transition-colors {{ $errorClass }}"
            @if($hasError) aria-invalid="{{ $ariaInvalid }}" aria-describedby="error-{{ $name }}" @endif>{{ old(str_replace(['[', ']'], ['.', ''], $name), $value) }}</textarea>
    @elseif($type === 'password')
        <div x-data="{ showPassword: false }" class="relative">
            <input type="password" name="{{ $name }}" id="{{ $name }}" value="{{ old(str_replace(['[', ']'], ['.', ''], $name), $value) }}" {{ $attributes }}
                {{ $required ? 'required' : '' }} placeholder="{{ $placeholder }}" autocomplete="{{ $autocomplete ?: 'new-password' }}"
                :type="showPassword ? 'text' : 'password'"
                class="w-full border rounded-lg px-3 py-2.5 text-sm transition-colors pr-10 {{ $errorClass }}"
                @if($hasError) aria-invalid="{{ $ariaInvalid }}" aria-describedby="error-{{ $name }}" @endif>
            <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    tabindex="-1">
                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" x-cloak aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            </button>
        </div>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old(str_replace(['[', ']'], ['.', ''], $name), $value) }}" {{ $attributes }}
            {{ $required ? 'required' : '' }} placeholder="{{ $placeholder }}" autocomplete="{{ $autocomplete ?: ($type === 'password' ? 'new-password' : 'off') }}"
            class="w-full border rounded-lg px-3 py-2.5 text-sm transition-colors {{ $errorClass }}"
            @if($hasError) aria-invalid="{{ $ariaInvalid }}" aria-describedby="error-{{ $name }}" @endif>
    @endif

    @if($hasError)
        <p id="error-{{ $name }}" class="text-red-500 text-xs mt-1">{{ $errors->first($errorKey) }}</p>
    @endif
</div>
