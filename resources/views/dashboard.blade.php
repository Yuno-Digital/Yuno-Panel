<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl leading-tight text-gray-900 dark:text-gray-100">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-2xl p-8 sm:p-10 bg-gradient-to-br from-brand-600 via-violet-600 to-fuchsia-600 shadow-glow-lg animate-fade-in-up">
                <div class="absolute -top-16 -right-10 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-20 -left-10 w-64 h-64 rounded-full bg-fuchsia-300/20 blur-3xl"></div>
                <div class="relative">
                    <p class="text-sm font-medium text-white/80">{{ __('Welcome back,') }} {{ Auth::user()->name }} 👋</p>
                    <h3 class="mt-1 text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ __('Your game servers, at a glance.') }}</h3>
                    <a href="{{ route('servers.index') }}"
                       class="mt-5 inline-flex items-center gap-2 rounded-lg bg-white/95 px-4 py-2 text-sm font-semibold text-brand-700 shadow-sm hover:bg-white hover:-translate-y-0.5 transition-all duration-200">
                        {{ __('Manage your servers') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
                    </a>
                </div>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="glass card-hover ring-1 ring-white/50 dark:ring-white/5 rounded-2xl p-6 animate-fade-in-up">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Servers') }}</div>
                        <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-indigo-500 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1zM5 14h14a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3a1 1 0 011-1z"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 text-4xl font-extrabold text-gray-900 dark:text-gray-100">{{ $stats['servers'] }}</div>
                </div>

                <div class="glass card-hover ring-1 ring-white/50 dark:ring-white/5 rounded-2xl p-6 animate-fade-in-up delay-75"
                     x-data="runningCount(@js($servers->map(fn ($s) => route('servers.stats', $s))->values()))" x-init="start()">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Running') }}</div>
                        <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 text-white">
                            <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                        </span>
                    </div>
                    <div class="mt-3 text-4xl font-extrabold text-emerald-500" x-text="running">{{ $stats['running'] }}</div>
                </div>

                @if (! is_null($stats['nodes']))
                    <div class="glass card-hover ring-1 ring-white/50 dark:ring-white/5 rounded-2xl p-6 animate-fade-in-up delay-150">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Nodes') }}</div>
                            <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-fuchsia-500 to-pink-500 text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                            </span>
                        </div>
                        <div class="mt-3 text-4xl font-extrabold text-gray-900 dark:text-gray-100">{{ $stats['nodes'] }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
