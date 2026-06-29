@csrf
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $user->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $user->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$user->exists ? __('New password (leave blank to keep)') : __('Password')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                      :required="! $user->exists" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <label class="inline-flex items-center">
        <input type="hidden" name="is_admin" value="0">
        <input type="checkbox" name="is_admin" value="1"
               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
               @checked(old('is_admin', $user->is_admin))>
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Administrator') }}</span>
    </label>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
