@csrf
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $role->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label :value="__('Permissions')" />
        <p class="mt-1 mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Administrator grants full access to everything.') }}</p>
        <div class="space-y-2">
            @foreach (\App\Models\Role::PERMISSIONS as $key => $label)
                <label class="flex items-start gap-2">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-indigo-600 dark:checked:bg-indigo-500 focus:ring-indigo-500"
                           @checked(in_array($key, old('permissions', $role->permissions ?? []), true))>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $key }}</span>
                        — {{ __($label) }}
                    </span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.roles.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
