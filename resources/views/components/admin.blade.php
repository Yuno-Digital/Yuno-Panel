@props(['title' => 'Admin'])

@php
    $groups = [
        'Overview' => [
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Overview'],
        ],
        'Management' => [
            ['route' => 'admin.nodes.index', 'pattern' => 'admin.nodes.*', 'label' => 'Nodes'],
            ['route' => 'admin.servers.index', 'pattern' => 'admin.servers.*', 'label' => 'Servers'],
            ['route' => 'admin.eggs.index', 'pattern' => 'admin.eggs.*', 'label' => 'Eggs'],
            ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Users'],
        ],
        'System' => [
            ['route' => 'admin.settings.index', 'pattern' => 'admin.settings.*', 'label' => 'Settings'],
        ],
        'API' => [
            ['route' => 'admin.api.application.index', 'pattern' => 'admin.api.application.*', 'label' => 'Application Keys'],
            ['route' => 'admin.api.client.index', 'pattern' => 'admin.api.client.*', 'label' => 'Client Keys'],
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __($title) }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            <aside class="md:w-56 shrink-0">
                <nav class="bg-white shadow-sm sm:rounded-lg p-3 space-y-4">
                    @foreach ($groups as $group => $items)
                        <div>
                            <p class="px-3 mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $group }}</p>
                            <ul class="space-y-1">
                                @foreach ($items as $item)
                                    <li>
                                        <a href="{{ route($item['route']) }}"
                                           class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs($item['pattern']) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                                            {{ __($item['label']) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>
            </aside>

            <main class="flex-1 min-w-0">
                @include('admin.partials.flash')
                {{ $slot }}
            </main>
        </div>
    </div>
</x-app-layout>
