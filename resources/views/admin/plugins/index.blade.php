<x-admin title="Plugins">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Plugins') }}</h3>
    </div>

    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        {{ __('Drop plugin folders into the panel\'s') }} <code>plugins/</code> {{ __('directory. Get plugins from the') }}
        <a href="https://github.com/Yuno-Digital/Yuno-Panel-Plugins" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ __('plugins repository') }}</a>.
    </p>

    @if (empty($plugins))
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-sm text-gray-500 dark:text-gray-400">
            {{ __('No plugins found. Clone a plugin into') }} <code>plugins/&lt;name&gt;/</code> {{ __('and it will appear here.') }}
        </div>
    @else
        <div class="space-y-3">
            @foreach ($plugins as $plugin)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $plugin['name'] }}</span>
                            @if ($plugin['version'])
                                <span class="text-xs font-mono text-gray-400">v{{ $plugin['version'] }}</span>
                            @endif
                            @if ($plugin['enabled'])
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">{{ __('Enabled') }}</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ __('Disabled') }}</span>
                            @endif
                        </div>
                        @if ($plugin['description'])
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $plugin['description'] }}</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-400">
                            <span class="font-mono">{{ $plugin['id'] }}</span>@if ($plugin['author']) · {{ $plugin['author'] }}@endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.plugins.toggle', $plugin['id']) }}" class="shrink-0">
                        @csrf
                        <input type="hidden" name="enabled" value="{{ $plugin['enabled'] ? '0' : '1' }}">
                        @if ($plugin['enabled'])
                            <x-secondary-button type="submit">{{ __('Disable') }}</x-secondary-button>
                        @else
                            <x-primary-button type="submit">{{ __('Enable') }}</x-primary-button>
                        @endif
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</x-admin>
