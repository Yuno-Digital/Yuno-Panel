<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Two-Factor Authentication') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Add an extra layer of security using an authenticator app (TOTP).') }}
        </p>
    </header>

    @if ($user->hasTwoFactorEnabled())
        {{-- Enabled --}}
        <div class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-green-700 dark:text-green-400">
            <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
            {{ __('Two-factor authentication is enabled.') }}
        </div>

        <div class="mt-4 rounded-md bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ __('Recovery codes') }}</p>
                <a href="{{ route('profile.2fa.recovery') }}" download
                   class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                    </svg>
                    {{ __('Download') }}
                </a>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-2">{{ __('Store these somewhere safe. Each can be used once if you lose your device.') }}</p>
            <div class="grid grid-cols-2 gap-1 font-mono text-sm text-gray-700 dark:text-gray-300">
                @foreach ($user->two_factor_recovery_codes ?? [] as $code)
                    <span>{{ $code }}</span>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('profile.2fa.destroy') }}" class="mt-4"
              data-confirm="Disable two-factor authentication?" data-confirm-button="Disable" data-confirm-icon="warning">
            @csrf @method('DELETE')
            <x-danger-button>
                {{ __('Disable') }}
            </x-danger-button>
        </form>

    @elseif ($twoFactorQr)
        {{-- Pending confirmation: show QR + ask for a code --}}
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Scan this QR code with your authenticator app, then enter the generated code to finish.') }}
        </p>
        <div class="mt-3 inline-block rounded-md bg-white p-3 border border-gray-200 dark:border-gray-700">
            <img src="{{ $twoFactorQr }}" alt="{{ __('Two-factor QR code') }}" class="w-48 h-48">
        </div>

        <form method="POST" action="{{ route('profile.2fa.confirm') }}" class="mt-4 flex items-end gap-4 max-w-sm">
            @csrf
            <div class="flex-1">
                <x-input-label for="code" :value="__('Authentication code')" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                              class="mt-1 block w-full" placeholder="123456" required />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>
            <x-primary-button>{{ __('Confirm') }}</x-primary-button>
        </form>

    @else
        {{-- Not set up --}}
        <form method="POST" action="{{ route('profile.2fa.store') }}" class="mt-4">
            @csrf
            <x-primary-button>{{ __('Enable') }}</x-primary-button>
        </form>
    @endif
</section>
