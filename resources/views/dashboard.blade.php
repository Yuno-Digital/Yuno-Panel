<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Servers') }}</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['servers'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6"
                     x-data="runningCount(@js($servers->map(fn ($s) => route('servers.stats', $s))->values()))" x-init="start()">
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Running') }}</div>
                    <div class="mt-2 text-3xl font-bold text-green-600" x-text="running">{{ $stats['running'] }}</div>
                </div>
                @if (! is_null($stats['nodes']))
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nodes') }}</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['nodes'] }}</div>
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-700 dark:text-gray-300">
                {{ __('Welcome to Yuno Panel.') }}
                <a href="{{ route('servers.index') }}" class="text-indigo-600 hover:underline">
                    {{ __('Manage your servers') }} &rarr;
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
