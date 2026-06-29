<x-guest-layout>
    <div x-data="{ recovery: false }">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <span x-show="!recovery">{{ __('Enter the code from your authenticator app to continue.') }}</span>
            <span x-show="recovery" x-cloak>{{ __('Enter one of your recovery codes.') }}</span>
        </p>

        <form method="POST" action="{{ route('two-factor.challenge.store') }}">
            @csrf

            <div x-show="!recovery">
                <x-input-label for="code" :value="__('Authentication code')" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric"
                              autocomplete="one-time-code" autofocus
                              class="block mt-1 w-full" placeholder="123456" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div x-show="recovery" x-cloak>
                <x-input-label for="recovery_code" :value="__('Recovery code')" />
                <x-text-input id="recovery_code" name="recovery_code" type="text"
                              class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:underline"
                        @click="recovery = !recovery">
                    <span x-show="!recovery">{{ __('Use a recovery code') }}</span>
                    <span x-show="recovery" x-cloak>{{ __('Use an authentication code') }}</span>
                </button>

                <x-primary-button>{{ __('Log in') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
