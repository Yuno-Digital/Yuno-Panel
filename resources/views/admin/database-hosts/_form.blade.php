@csrf
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $host->name)" placeholder="Primary MySQL" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="sm:col-span-2">
            <x-input-label for="host" :value="__('Host')" />
            <x-text-input id="host" name="host" type="text" class="mt-1 block w-full" :value="old('host', $host->host)" placeholder="127.0.0.1" required />
            <x-input-error :messages="$errors->get('host')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="port" :value="__('Port')" />
            <x-text-input id="port" name="port" type="number" class="mt-1 block w-full" :value="old('port', $host->port ?? 3306)" required />
            <x-input-error :messages="$errors->get('port')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="username" :value="__('Admin username')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $host->username)" placeholder="root" required />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password" :value="__('Admin password')" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password"
                          :value="''" :placeholder="$host->exists ? __('leave blank to keep') : ''" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="linked_host" :value="__('Connect host (optional)')" />
            <x-text-input id="linked_host" name="linked_host" type="text" class="mt-1 block w-full" :value="old('linked_host', $host->linked_host)" placeholder="db.example.com" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Hostname shown to server owners for connecting. Defaults to Host.') }}</p>
        </div>
        <div>
            <x-input-label for="max_databases" :value="__('Max databases (optional)')" />
            <x-text-input id="max_databases" name="max_databases" type="number" class="mt-1 block w-full" :value="old('max_databases', $host->max_databases)" placeholder="{{ __('unlimited') }}" />
        </div>
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save & test') }}</x-primary-button>
        <a href="{{ route('admin.database-hosts.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
