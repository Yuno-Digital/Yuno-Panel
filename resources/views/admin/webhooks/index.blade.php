<x-admin title="Webhooks">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Webhooks') }}</h3>
        <a href="{{ route('admin.webhooks.create') }}">
            <x-primary-button>{{ __('New webhook') }}</x-primary-button>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/40">
                <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <th class="px-6 py-3">{{ __('Name') }}</th>
                    <th class="px-6 py-3">{{ __('URL') }}</th>
                    <th class="px-6 py-3">{{ __('Events') }}</th>
                    <th class="px-6 py-3">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                @forelse ($webhooks as $webhook)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $webhook->name }}</td>
                        <td class="px-6 py-4 font-mono text-xs truncate max-w-xs">{{ $webhook->url }}</td>
                        <td class="px-6 py-4">{{ count($webhook->events ?? []) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $webhook->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $webhook->is_active ? __('Active') : __('Disabled') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.webhooks.edit', $webhook) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}"
                                      data-confirm="Delete this webhook?" data-confirm-button="Delete">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-gray-500 dark:text-gray-400">{{ __('No webhooks yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin>
