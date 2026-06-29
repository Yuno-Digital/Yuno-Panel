<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Appearance') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Choose how the panel looks. "System" follows your operating system setting.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('profile.theme') }}" class="mt-6">
        @csrf
        @method('PATCH')

        <div x-data="{ theme: '{{ $user->theme ?? 'system' }}' }"
             class="grid grid-cols-3 gap-3 max-w-md"
             x-init="theme = localStorage.getItem('theme') || theme">
            @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="{{ $value }}" class="sr-only peer"
                           x-model="theme"
                           @change="window.__setTheme && window.__setTheme('{{ $value }}')">
                    <div class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-500">
                        {{ __($label) }}
                    </div>
                </label>
            @endforeach
        </div>

        <div class="flex items-center gap-4 mt-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'theme-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
