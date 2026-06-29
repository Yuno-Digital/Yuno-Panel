<x-admin title="Overview">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Users') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['users'] }}</div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['admins'] }} {{ __('admins') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nodes') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['nodes'] }}</div>
            <div class="mt-1 text-xs text-green-600">{{ $stats['nodes_online'] }} {{ __('online') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Servers') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['servers'] }}</div>
            <div class="mt-1 text-xs text-green-600">{{ $stats['servers_running'] }} {{ __('running') }}</div>
        </div>
    </div>

    {{-- Panel version & update status --}}
    <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Panel version') }}</div>
                <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">v{{ $update['current'] }}</div>
            </div>

            <div>
                @if ($update['available'])
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 px-3 py-1 text-sm font-semibold">
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ __('Update available') }}: v{{ $update['latest'] }}
                    </span>
                    @if ($update['url'])
                        <a href="{{ $update['url'] }}" target="_blank" rel="noopener"
                           class="ms-3 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ __('View release') }} &rarr;
                        </a>
                    @endif
                @elseif ($update['checked'])
                    <span class="inline-flex items-center gap-2 rounded-full bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 px-3 py-1 text-sm font-semibold">
                        <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                        {{ __('Up to date') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-3 py-1 text-sm font-medium">
                        {{ __('Could not check for updates') }}
                    </span>
                @endif
            </div>
        </div>

        @if ($update['available'] && $update['changelog'])
            <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ __('Changelog') }} (v{{ $update['latest'] }})</h4>
                <pre class="max-h-72 overflow-auto whitespace-pre-wrap rounded-md bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 p-4 text-xs leading-relaxed text-gray-700 dark:text-gray-300 font-mono">{{ $update['changelog'] }}</pre>
            </div>
        @endif
    </div>
</x-admin>
