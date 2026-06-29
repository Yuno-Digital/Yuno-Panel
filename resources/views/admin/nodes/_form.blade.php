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
                          :value="old('fqdn', $node->fqdn)" required />
            <x-input-error :messages="$errors->get('fqdn')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="daemon_port" :value="__('Daemon port')" />
            <x-text-input id="daemon_port" name="daemon_port" type="number" class="mt-1 block w-full"
                          :value="old('daemon_port', $node->daemon_port ?? 8080)" required />
            <x-input-error :messages="$errors->get('daemon_port')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="memory_mb" :value="__('Memory (MB)')" />
            <x-text-input id="memory_mb" name="memory_mb" type="number" class="mt-1 block w-full"
                          :value="old('memory_mb', $node->memory_mb ?? 0)" required />
            <x-input-error :messages="$errors->get('memory_mb')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="disk_mb" :value="__('Disk (MB)')" />
            <x-text-input id="disk_mb" name="disk_mb" type="number" class="mt-1 block w-full"
                          :value="old('disk_mb', $node->disk_mb ?? 0)" required />
            <x-input-error :messages="$errors->get('disk_mb')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $node->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <label class="inline-flex items-center">
        <input type="hidden" name="is_online" value="0">
        <input type="checkbox" name="is_online" value="1"
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
               @checked(old('is_online', $node->is_online))>
        <span class="ms-2 text-sm text-gray-600">{{ __('Mark as online') }}</span>
    </label>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.nodes.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
