<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.theme')

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 overflow-hidden bg-gradient-to-br from-slate-50 via-indigo-50 to-fuchsia-50 dark:from-[#0a0a12] dark:via-[#0d0b1a] dark:to-[#0a0714]">
            <div class="aurora"></div>

            <div class="relative z-10 flex flex-col items-center w-full px-4">
                <a href="/" class="flex items-center gap-3 animate-fade-in-up">
                    <span class="grid place-items-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-fuchsia-500 shadow-glow animate-float">
                        <x-application-logo class="w-9 h-9 fill-current text-white" />
                    </span>
                    <span class="text-3xl font-extrabold tracking-tight text-gradient">{{ config('app.name', 'Yuno') }}</span>
                </a>

                <div class="w-full sm:max-w-md mt-8 px-7 py-6 glass ring-1 ring-white/50 dark:ring-white/10 shadow-glow-lg rounded-2xl animate-scale-in">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-xs text-gray-400 dark:text-gray-500 animate-fade-in delay-300">{{ __('Game server management, reimagined.') }}</p>
            </div>
        </div>
    </body>
</html>
