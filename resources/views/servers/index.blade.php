<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Servers') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                <th class="px-6 py-3">{{ __('Address') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($servers as $server)
                                <tr class="text-sm text-gray-700 dark:text-gray-300">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('servers.show', $server) }}" class="hover:text-indigo-600 hover:underline">{{ $server->name }}</a>
                                    </td>
                                    <td class="px-6 py-4" x-data="serverStatus('{{ route('servers.stats', $server) }}')" x-init="start()">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold" :class="badge">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="dot"></span>
                                            <span x-text="label"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $server->node?->name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $server->owner?->name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ \App\Support\Format::size($server->memory_mb) }}</td>
                                    <td class="px-6 py-4 font-mono text-xs">{{ $server->allocation?->address() ?? '—' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('servers.show', $server) }}" class="text-indigo-600 hover:underline">{{ __('Manage') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
