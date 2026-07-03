<x-admin :title="$title">
    <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __($title) }}</h3>
    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        {{ __('Application keys authenticate admin-level access to the panel API.') }}
    </p>

    @if (session('new_key'))
        <div class="mb-4 rounded-md bg-amber-50 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 px-4 py-3">
            <p class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ __('Your new API key (shown once):') }}</p>
            <code class="mt-1 block break-all rounded bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 px-2 py-1 font-mono text-sm text-gray-800 dark:text-gray-100">{{ session('new_key') }}</code>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.api.application.store') }}" class="flex items-end gap-4">
            @csrf
            <div class="flex-1">
                <x-input-label for="memo" :value="__('Description (optional)')" />
                <x-text-input id="memo" name="memo" type="text" class="mt-1 block w-full"
                              :value="old('memo')" placeholder="e.g. CI deployment" />
                <x-input-error :messages="$errors->get('memo')" class="mt-2" />
            </div>
            <x-primary-button>{{ __('Create key') }}</x-primary-button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
        @if ($keys->isEmpty())
            <div class="p-6 text-gray-500 dark:text-gray-400">{{ __('No keys yet.') }}</div>
        @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-3">{{ __('Identifier') }}</th>
                        <th class="px-6 py-3">{{ __('Description') }}</th>
                        <th class="px-6 py-3">{{ __('Last used') }}</th>
                        <th class="px-6 py-3">{{ __('Created') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                    @foreach ($keys as $key)
                        <tr>
                            <td class="px-6 py-4 font-mono text-xs">{{ $key->identifier }}</td>
                            <td class="px-6 py-4">{{ $key->memo ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $key->last_used_at?->diffForHumans() ?? __('never') }}</td>
                            <td class="px-6 py-4">{{ $key->created_at->toDateString() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <form method="POST" action="{{ route('admin.api.keys.destroy', $key) }}"
                                          data-confirm="Revoke this key?" data-confirm-button="Revoke">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('Revoke') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-admin>
