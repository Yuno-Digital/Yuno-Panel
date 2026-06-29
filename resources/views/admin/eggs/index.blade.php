<x-admin title="Eggs">
    <div x-data="{ importing: false }" class="mb-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Eggs') }}</h3>
            <div class="flex items-center gap-2">
                <x-secondary-button type="button" @click="importing = ! importing">{{ __('Import egg') }}</x-secondary-button>
                <a href="{{ route('admin.eggs.create') }}">
                    <x-primary-button>{{ __('New egg') }}</x-primary-button>
                </a>
            </div>
        </div>

        <div x-show="importing" x-cloak class="mt-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 ring-1 ring-gray-100 dark:ring-gray-700/60">
            <form method="POST" action="{{ route('admin.eggs.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                @csrf
                <div class="flex-1 min-w-64">
                    <x-input-label for="egg_file" :value="__('Egg JSON file')" />
                    <input id="egg_file" name="egg_file" type="file" accept=".json,application/json" required
                           class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-white file:cursor-pointer">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Upload a Pterodactyl / Pelican egg export (.json).') }}</p>
                    <x-input-error :messages="$errors->get('egg_file')" class="mt-2" />
                </div>
                <x-primary-button>{{ __('Import') }}</x-primary-button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
        @if ($eggs->isEmpty())
            <div class="p-6 text-gray-500 dark:text-gray-400">{{ __('No eggs yet. Eggs are reusable server templates (image + startup command).') }}</div>
        @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-3">{{ __('Name') }}</th>
                        <th class="px-6 py-3">{{ __('Author') }}</th>
                        <th class="px-6 py-3">{{ __('Docker image') }}</th>
                        <th class="px-6 py-3">{{ __('Variables') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                    @foreach ($eggs as $egg)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $egg->name }}</td>
                            <td class="px-6 py-4">{{ $egg->author ?? '—' }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $egg->docker_image }}</td>
                            <td class="px-6 py-4">{{ $egg->variables_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.eggs.edit', $egg) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.eggs.destroy', $egg) }}"
                                          onsubmit="return confirm('Delete this egg?');">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $eggs->links() }}</div>
</x-admin>
