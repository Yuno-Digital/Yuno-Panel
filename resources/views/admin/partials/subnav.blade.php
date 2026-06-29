@php
    $tabs = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Overview'],
        ['route' => 'admin.nodes.index', 'pattern' => 'admin.nodes.*', 'label' => 'Nodes'],
        ['route' => 'admin.servers.index', 'pattern' => 'admin.servers.*', 'label' => 'Servers'],
        ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => 'Users'],
    ];
@endphp
<nav class="mb-6 flex flex-wrap gap-2">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs($tab['pattern']) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
