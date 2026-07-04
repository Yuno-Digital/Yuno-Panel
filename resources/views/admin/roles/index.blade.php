<x-admin title="Roles">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Roles') }}</h3>
        <a href="{{ route('admin.roles.create') }}">
            <x-primary-button>{{ __('New role') }}</x-primary-button>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/40">
                <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <th class="px-6 py-3">{{ __('Name') }}</th>
                    <th class="px-6 py-3">{{ __('Permissions') }}</th>
                    <th class="px-6 py-3">{{ __('Users') }}</th>
                    <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                @forelse ($roles as $role)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</td>
                        <td class="px-6 py-4">
                            @if (in_array('administrator', $role->permissions ?? [], true))
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">{{ __('Administrator') }}</span>
                            @else
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ trans_choice(':count permission|:count permissions', count($role->permissions ?? []), ['count' => count($role->permissions ?? [])]) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $role->users_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                      data-confirm="Delete this role? Users keep access but lose this role." data-confirm-button="Delete">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-6 text-gray-500 dark:text-gray-400">{{ __('No roles yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin>
