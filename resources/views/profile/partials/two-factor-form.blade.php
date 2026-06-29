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

        @if (session('status') === 'two-factor-confirmed' && session('new_key') === null)
            <div class="mt-4 rounded-md bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ __('Recovery codes') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('Store these somewhere safe. Each can be used once if you lose your device.') }}</p>
                <div class="grid grid-cols-2 gap-1 font-mono text-sm text-gray-700 dark:text-gray-300">
                    @foreach ($user->two_factor_recovery_codes ?? [] as $code)
                        <span>{{ $code }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.2fa.destroy') }}" class="mt-4">
            @csrf @method('DELETE')
            <x-danger-button onclick="return confirm('Disable two-factor authentication?')">
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
