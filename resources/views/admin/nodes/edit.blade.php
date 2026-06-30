<x-admin title="Nodes">
    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit node') }}: {{ $node->name }}</h3>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.nodes.update', $node) }}">
            @method('PUT')
            @include('admin.nodes._form')
        </form>
    </div>

    {{-- Allocations: create the IP:port slots servers bind to (Pelican-style) --}}
    <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Allocations') }}</h4>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create IP:port allocations here, then assign them to servers.') }}</p>

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
</x-admin>
