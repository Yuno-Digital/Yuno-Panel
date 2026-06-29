<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('API Keys') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Client API keys let external tools act on your account. The secret is shown only once.') }}
        </p>
    </header>

    @if (session('status') === 'api-key-created' && session('new_key'))
        <div class="mt-4 rounded-md bg-amber-50 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 px-4 py-3">
            <p class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ __('Your new API key (copy it now):') }}</p>
            <code class="mt-1 block break-all rounded bg-white dark:bg-gray-900 border border-amber-200 dark:border-amber-700 px-2 py-1 font-mono text-sm text-gray-800 dark:text-gray-100">{{ session('new_key') }}</code>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.api-keys.store') }}" class="mt-6 flex items-end gap-4">
        @csrf
        <div class="flex-1">
            <x-input-label for="memo" :value="__('Description (optional)')" />
            <x-text-input id="memo" name="memo" type="text" class="mt-1 block w-full"
                          :value="old('memo')" placeholder="e.g. my deploy script" />
            <x-input-error :messages="$errors->get('memo')" class="mt-2" />
        </div>
        <x-primary-button>{{ __('Create key') }}</x-primary-button>
    </form>

    <div class="mt-6">
        @if ($apiKeys->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('You have no API keys yet.') }}</p>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-md">
                @foreach ($apiKeys as $key)
                    <li class="flex items-center justify-between px-4 py-3">
                        <div>
                            <p class="font-mono text-xs text-gray-700 dark:text-gray-300">{{ $key->identifier }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $key->memo ?? __('No description') }} · {{ $key->created_at->toDateString() }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('profile.api-keys.destroy', $key) }}"
                              onsubmit="return confirm('Revoke this key?');">
                            @csrf @method('DELETE')
                            <button class="text-sm text-red-600 hover:underline">{{ __('Revoke') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
