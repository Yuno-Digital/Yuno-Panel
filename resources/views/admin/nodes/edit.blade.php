<x-admin title="Nodes">
    <div x-data="{ tab: 'settings' }">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit node') }}: {{ $node->name }}</h3>
            <span class="inline-flex items-center gap-2 text-sm font-medium {{ $node->is_online ? 'text-green-600' : 'text-gray-500' }}">
                <span class="w-2 h-2 rounded-full {{ $node->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                {{ $node->is_online ? __('Online') : __('Offline') }}
            </span>
        </div>

        @include('admin.eggs._tabnav', ['tabs' => [
            'settings' => __('Settings'),
            'allocations' => __('Allocations').' ('.$node->allocations->count().')',
            'deploy' => __('Auto Deploy'),
        ]])

        {{-- Settings --}}
        <div x-show="tab === 'settings'" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
            <form method="POST" action="{{ route('admin.nodes.update', $node) }}">
                @method('PUT')
                @include('admin.nodes._form')
            </form>
        </div>

        {{-- Allocations --}}
        <div x-show="tab === 'allocations'" x-cloak class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Create IP:port allocations here, then assign them to servers.') }}</p>

            <form method="POST" action="{{ route('admin.nodes.allocations.store', $node) }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <x-input-label for="ip" :value="__('IP')" />
                    <x-text-input id="ip" name="ip" type="text" class="mt-1 block w-40 font-mono text-sm" :value="old('ip', '0.0.0.0')" required />
                </div>
                <div class="flex-1 min-w-48">
                    <x-input-label for="ports" :value="__('Ports')" />
                    <x-text-input id="ports" name="ports" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('ports')" placeholder="25565, 25570-25580" required />
                </div>
                <x-primary-button>{{ __('Add') }}</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('ports')" class="mt-2" />

            <div class="mt-5">
                @if ($node->allocations->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No allocations yet.') }}</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach ($node->allocations as $allocation)
                            <div class="inline-flex items-center gap-2 rounded-md border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm">
                                <span class="font-mono text-gray-800 dark:text-gray-200">{{ $allocation->address() }}</span>
                                @if ($allocation->server)
                                    <span class="text-xs rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5">{{ $allocation->server->name }}</span>
                                @else
                                    <span class="text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 px-2 py-0.5">{{ __('free') }}</span>
                                    <form method="POST" action="{{ route('admin.nodes.allocations.destroy', [$node, $allocation]) }}"
                                          onsubmit="return confirm('Delete this allocation?');">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-700" title="{{ __('Delete') }}">&times;</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Auto Deploy --}}
        @php
            $configureCmd = sprintf('sudo yuno-wings configure --panel-url %s --token %s --node %d',
                rtrim(config('app.url'), '/'), $node->daemon_token, $node->id);
        @endphp
        <div x-show="tab === 'deploy'" x-cloak class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
            <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Auto Deploy') }}</h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Run this on the node host. The daemon fetches its config (including the token) from the panel and writes config.json, so the token stays the same across restarts.') }}
            </p>

            <div class="mt-4" x-data="{ copied: false }">
                <div class="relative">
                    <pre class="overflow-auto rounded-md bg-gray-900 text-gray-100 p-4 text-xs font-mono whitespace-pre-wrap pr-20">{{ $configureCmd }}</pre>
                    <button type="button"
                            @click="navigator.clipboard.writeText(@js($configureCmd)); copied = true; setTimeout(() => copied = false, 1500)"
                            class="absolute top-2 right-2 rounded-md bg-indigo-600 text-white text-xs px-2 py-1 hover:bg-indigo-700">
                        <span x-text="copied ? '{{ __('Copied') }}' : '{{ __('Copy') }}'"></span>
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Then start the daemon:') }} <code class="text-gray-700 dark:text-gray-300">yuno-wings</code></p>
            </div>

            <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ __('Daemon token') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Generated by the panel. Regenerating invalidates the old one.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.nodes.regenerate-token', $node) }}"
                          onsubmit="return confirm('Regenerate the token? The node must be reconfigured.');">
                        @csrf
                        <x-secondary-button>{{ __('Regenerate') }}</x-secondary-button>
                    </form>
                </div>
                <code class="mt-2 block break-all rounded bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 px-2 py-1 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $node->daemon_token }}</code>
            </div>
        </div>
    </div>
</x-admin>
