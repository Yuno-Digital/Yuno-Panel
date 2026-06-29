<x-admin title="Servers">
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Servers') }}</h3>
                <a href="{{ route('admin.servers.create') }}">
                    <x-primary-button>{{ __('New server') }}</x-primary-button>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                @if ($servers->isEmpty())
                    <div class="p-6 text-gray-500 dark:text-gray-400">{{ __('No servers yet.') }}</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">{{ __('Name') }}</th>
                                <th class="px-6 py-3">{{ __('Status') }}</th>
                                <th class="px-6 py-3">{{ __('Node') }}</th>
                                <th class="px-6 py-3">{{ __('Owner') }}</th>
                                <th class="px-6 py-3">{{ __('Memory') }}</th>
                                <th class="px-6 py-3">{{ __('Port') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                            @foreach ($servers as $server)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $server->name }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $color = match ($server->status) {
                                                'running' => 'bg-green-100 text-green-800',
                                                'starting', 'stopping' => 'bg-yellow-100 text-yellow-800',
                                                default => 'bg-gray-100 text-gray-700 dark:text-gray-300',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $color }}">
                                            {{ ucfirst($server->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $server->node?->name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $server->owner?->name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ \App\Support\Format::size($server->memory_mb) }}</td>
                                    <td class="px-6 py-4">{{ $server->port ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.servers.edit', $server) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('admin.servers.destroy', $server) }}"
                                                  onsubmit="return confirm('Delete this server?');">
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

            <div class="mt-4">{{ $servers->links() }}</div>
</x-admin>
