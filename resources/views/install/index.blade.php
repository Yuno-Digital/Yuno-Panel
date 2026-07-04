<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Install') }} · {{ config('app.name', 'Yuno Panel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="min-h-screen py-12 px-4 flex flex-col items-center">
        <div class="w-full max-w-2xl">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold">{{ config('app.name', 'Yuno Panel') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Web installer') }}</p>
            </div>

            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-200">{{ session('error') }}</div>
            @endif

            {{-- Requirements --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium mb-4">{{ __('Requirements') }}</h2>
                <ul class="space-y-2 text-sm">
                    @foreach ($requirements as $req)
                        <li class="flex items-center justify-between">
                            <span class="text-gray-700 dark:text-gray-300">{{ $req['label'] }}</span>
                            @if ($req['ok'])
                                <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-medium">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    {{ __('OK') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400 font-medium">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    {{ __('Missing') }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Admin account --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-medium mb-1">{{ __('Administrator account') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('This runs migrations and creates your admin user.') }}</p>

                @unless ($met)
                    <p class="mb-4 text-sm text-red-600 dark:text-red-400">{{ __('Resolve the failed requirements above, then reload this page.') }}</p>
                @endunless

                <form method="POST" action="{{ route('install.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', 'Admin')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    </div>
                    <div class="pt-2">
                        <x-primary-button type="submit" :disabled="! $met">{{ __('Install panel') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
