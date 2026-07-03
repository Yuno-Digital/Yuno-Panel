@csrf
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $node->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="sm:col-span-2">
            <x-input-label for="fqdn" :value="__('FQDN / IP')" />
            <x-text-input id="fqdn" name="fqdn" type="text" class="mt-1 block w-full"
                          :value="old('fqdn', $node->fqdn)" placeholder="node01.example.com" required />
            <x-input-error :messages="$errors->get('fqdn')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="daemon_port" :value="__('Daemon port')" />
            <x-text-input id="daemon_port" name="daemon_port" type="number" class="mt-1 block w-full"
                          :value="old('daemon_port', $node->daemon_port ?? 8080)" required />
            <x-input-error :messages="$errors->get('daemon_port')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $node->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="rounded-md bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Memory and disk are detected automatically from the node when you save.') }}
        @if ($node->exists && $node->memory_mb)
            <span class="block mt-1 text-gray-800 dark:text-gray-100">
                {{ __('Currently detected:') }}
                <strong>{{ \App\Support\Format::size($node->memory_mb) }}</strong> {{ __('RAM') }},
                <strong>{{ \App\Support\Format::size($node->disk_mb) }}</strong> {{ __('disk') }}.
            </span>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save & detect') }}</x-primary-button>
        <a href="{{ route('admin.nodes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
