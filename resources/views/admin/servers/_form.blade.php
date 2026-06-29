@csrf
@php
    $selectClass = 'mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
@endphp
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $server->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="node_id" :value="__('Node')" />
            <select id="node_id" name="node_id" class="{{ $selectClass }}" required>
                <option value="">{{ __('— Select node —') }}</option>
                @foreach ($nodes as $n)
                    <option value="{{ $n->id }}" @selected(old('node_id', $server->node_id) == $n->id)>{{ $n->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('node_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="owner_id" :value="__('Owner')" />
            <select id="owner_id" name="owner_id" class="{{ $selectClass }}" required>
                <option value="">{{ __('— Select owner —') }}</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $server->owner_id) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('owner_id')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" class="{{ $selectClass }}" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $server->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="port" :value="__('Port')" />
            <x-text-input id="port" name="port" type="number" class="mt-1 block w-full"
                          :value="old('port', $server->port)" />
            <x-input-error :messages="$errors->get('port')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="memory_mb" :value="__('Memory (MB)')" />
            <x-text-input id="memory_mb" name="memory_mb" type="number" class="mt-1 block w-full"
                          :value="old('memory_mb', $server->memory_mb)" required />
            <x-input-error :messages="$errors->get('memory_mb')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="disk_mb" :value="__('Disk (MB)')" />
            <x-text-input id="disk_mb" name="disk_mb" type="number" class="mt-1 block w-full"
                          :value="old('disk_mb', $server->disk_mb)" required />
            <x-input-error :messages="$errors->get('disk_mb')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.servers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
