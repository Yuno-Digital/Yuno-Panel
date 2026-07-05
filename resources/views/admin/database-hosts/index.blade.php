<x-admin title="Database Hosts">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Database Hosts') }}</h3>
        <a href="{{ route('admin.database-hosts.create') }}"><x-primary-button>{{ __('New host') }}</x-primary-button></a>
    </div>

    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        {{ __('MySQL/MariaDB servers the panel creates per-server databases on. The admin credentials need permission to CREATE DATABASE, CREATE USER and GRANT.') }}
    </p>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
        @if ($hosts->isEmpty())
            <div class="p-6 text-sm text-gray-500 dark:text-gray-400">{{ __('No database hosts yet.') }}</div>
        @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/40 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">{{ __('Name') }}</th>
                        <th class="px-6 py-3">{{ __('Host') }}</th>
                        <th class="px-6 py-3">{{ __('Databases') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-700 dark:text-gray-300">
                    @foreach ($hosts as $host)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $host->name }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $host->host }}:{{ $host->port }}</td>
                            <td class="px-6 py-4">{{ $host->databases_count }}@if ($host->max_databases) / {{ $host->max_databases }}@endif</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.database-hosts.edit', $host) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.database-hosts.destroy', $host) }}"
                                          data-confirm="Delete this host? Server databases records are removed (the actual databases are not dropped)." data-confirm-button="Delete">
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
</x-admin>
