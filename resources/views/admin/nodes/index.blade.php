<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Admin') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.subnav')
            @include('admin.partials.flash')

            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Nodes') }}</h3>
                <a href="{{ route('admin.nodes.create') }}">
                    <x-primary-button>{{ __('New node') }}</x-primary-button>
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @if ($nodes->isEmpty())
                    <div class="p-6 text-gray-500">{{ __('No nodes yet.') }}</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3">{{ __('Name') }}</th>
                                <th class="px-6 py-3">{{ __('FQDN') }}</th>
                                <th class="px-6 py-3">{{ __('Status') }}</th>
                                <th class="px-6 py-3">{{ __('Servers') }}</th>
                                <th class="px-6 py-3">{{ __('Memory') }}</th>
                                <th class="px-6 py-3">{{ __('Disk') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                            @foreach ($nodes as $node)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $node->name }}</td>
                                    <td class="px-6 py-4">{{ $node->fqdn }}:{{ $node->daemon_port }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $node->is_online ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $node->is_online ? __('Online') : __('Offline') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $node->servers_count }}</td>
                                    <td class="px-6 py-4">{{ \App\Support\Format::size($node->memory_mb) }}</td>
                                    <td class="px-6 py-4">{{ \App\Support\Format::size($node->disk_mb) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.nodes.refresh', $node) }}">
                                                @csrf
                                                <button class="text-gray-600 hover:underline">{{ __('Refresh') }}</button>
                                            </form>
                                            <a href="{{ route('admin.nodes.edit', $node) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('admin.nodes.destroy', $node) }}"
                                                  onsubmit="return confirm('Delete this node?');">
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

            <div class="mt-4">{{ $nodes->links() }}</div>
        </div>
    </div>
</x-app-layout>
