<x-admin title="Users">
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Users') }}</h3>
                <a href="{{ route('admin.users.create') }}">
                    <x-primary-button>{{ __('New user') }}</x-primary-button>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">{{ __('Name') }}</th>
                            <th class="px-6 py-3">{{ __('Email') }}</th>
                            <th class="px-6 py-3">{{ __('Role') }}</th>
                            <th class="px-6 py-3">{{ __('Servers') }}</th>
                            <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->isAdmin() ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $user->isAdmin() ? __('Admin') : __('User') }}
                                        </span>
                                        @if ($user->role)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $user->role->name }}</span>
                                        @endif
                                        @if ($user->is_root)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Main admin') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $user->servers_count }}</td>
                                <td class="px-6 py-4">
                                    @php $canManage = ! $user->is_root || $user->is(auth()->user()); @endphp
                                    <div class="flex justify-end gap-2">
                                        @if ($canManage)
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        @else
                                            <span class="text-xs text-gray-400">{{ __('Protected') }}</span>
                                        @endif
                                        @unless ($user->is_root || $user->is(auth()->user()))
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                  data-confirm="Delete this user?" data-confirm-button="Delete">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $users->links() }}</div>
</x-admin>
