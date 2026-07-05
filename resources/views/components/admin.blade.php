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
            ['route' => 'admin.roles.index', 'pattern' => 'admin.roles.*', 'label' => 'Roles'],
        ],
        'System' => [
            ['route' => 'admin.settings.index', 'pattern' => 'admin.settings.*', 'label' => 'Settings'],
            ['route' => 'admin.webhooks.index', 'pattern' => 'admin.webhooks.*', 'label' => 'Webhooks'],
            ['route' => 'admin.plugins.index', 'pattern' => 'admin.plugins.*', 'label' => 'Plugins'],
        ],
        'API' => [
            ['route' => 'admin.api.application.index', 'pattern' => 'admin.api.application.*', 'label' => 'Application Keys'],
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl leading-tight text-gray-900 dark:text-gray-100">{{ __($title) }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            <aside class="md:w-56 shrink-0">
                <nav class="glass ring-1 ring-white/50 dark:ring-white/5 shadow-sm rounded-2xl p-3 space-y-4 md:sticky md:top-20">
                    @foreach ($groups as $group => $items)
                        <div>
                            <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $group }}</p>
                            <ul class="space-y-1">
                                @foreach ($items as $item)
                                    @php $active = request()->routeIs($item['pattern']); @endphp
                                    <li>
                                        <a href="{{ route($item['route']) }}"
                                           @class([
                                               'block px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                                               'bg-gradient-to-r from-brand-500 to-fuchsia-500 text-white shadow-glow' => $active,
                                               'text-gray-600 dark:text-gray-300 hover:bg-white/70 dark:hover:bg-white/5 hover:translate-x-0.5' => ! $active,
                                           ])>
                                            {{ __($item['label']) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>
            </aside>

            <main class="flex-1 min-w-0 animate-fade-in-up">
                @include('admin.partials.flash')
                {{ $slot }}
            </main>
        </div>
    </div>
</x-app-layout>
