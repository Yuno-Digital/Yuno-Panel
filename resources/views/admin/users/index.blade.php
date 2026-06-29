<x-admin title="Users">
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Users') }}</h3>
                <a href="{{ route('admin.users.create') }}">
                    <x-primary-button>{{ __('New user') }}</x-primary-button>
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3">{{ __('Name') }}</th>
                            <th class="px-6 py-3">{{ __('Email') }}</th>
                            <th class="px-6 py-3">{{ __('Role') }}</th>
                            <th class="px-6 py-3">{{ __('Servers') }}</th>
                            <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->is_admin ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $user->is_admin ? __('Admin') : __('User') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $user->servers_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        @unless ($user->is(auth()->user()))
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                  onsubmit="return confirm('Delete this user?');">
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
