<x-admin title="Plugins">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Plugins') }}</h3>
        <form method="POST" action="{{ route('admin.plugins.clear-cache') }}">
            @csrf
            <x-secondary-button type="submit">{{ __('Clear cache') }}</x-secondary-button>
        </form>
    </div>

    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        {{ __('Install plugins from the') }}
        <a href="https://github.com/{{ config('yuno.plugins_repository') }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ __('plugins repository') }}</a>
        {{ __('with one click, or drop folders into the panel\'s') }} <code>plugins/</code> {{ __('directory.') }}
    </p>

    {{-- Available from the repository (one-click install) --}}
    @if (! empty($available))
        <div class="mb-6">
            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Available to install') }}</h4>
            <div class="space-y-3">
                @foreach ($available as $plugin)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $plugin['name'] ?? $plugin['id'] }}</span>
                                @if (! empty($plugin['version']))
                                    <span class="text-xs font-mono text-gray-400">v{{ $plugin['version'] }}</span>
                                @endif
                            </div>
                            @if (! empty($plugin['description']))
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $plugin['description'] }}</p>
                            @endif
                            <p class="mt-1 text-xs text-gray-400">
                                <span class="font-mono">{{ $plugin['id'] }}</span>@if (! empty($plugin['author'])) · {{ $plugin['author'] }}@endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.plugins.install') }}" class="shrink-0"
                              data-confirm="Install {{ $plugin['name'] ?? $plugin['id'] }} from the plugins repository?" data-confirm-button="Install" data-confirm-icon="question">
                            @csrf
                            <input type="hidden" name="id" value="{{ $plugin['id'] }}">
                            <x-primary-button type="submit">{{ __('Install') }}</x-primary-button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Installed') }}</h4>
    @endif

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
                    <div class="shrink-0 flex items-center gap-2">
                        @if (! empty($plugin['settings']) || ! empty($plugin['info']))
                            <a href="{{ route('admin.plugins.settings', $plugin['id']) }}">
                                <x-secondary-button type="button">{{ __('Settings') }}</x-secondary-button>
                            </a>
                        @endif
                        <form method="POST" action="{{ route('admin.plugins.toggle', $plugin['id']) }}">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ $plugin['enabled'] ? '0' : '1' }}">
                            @if ($plugin['enabled'])
                                <x-secondary-button type="submit">{{ __('Disable') }}</x-secondary-button>
                            @else
                                <x-primary-button type="submit">{{ __('Enable') }}</x-primary-button>
                            @endif
                        </form>
                        <form method="POST" action="{{ route('admin.plugins.uninstall', $plugin['id']) }}"
                              data-confirm="Uninstall {{ $plugin['name'] }}? Its files and settings are removed." data-confirm-button="Uninstall">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:underline">{{ __('Uninstall') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin>
