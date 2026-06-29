<x-admin title="Overview">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500">{{ __('Users') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['users'] }}</div>
            <div class="mt-1 text-xs text-gray-500">{{ $stats['admins'] }} {{ __('admins') }}</div>
        </div>
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500">{{ __('Nodes') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['nodes'] }}</div>
            <div class="mt-1 text-xs text-green-600">{{ $stats['nodes_online'] }} {{ __('online') }}</div>
        </div>
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500">{{ __('Servers') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['servers'] }}</div>
            <div class="mt-1 text-xs text-green-600">{{ $stats['servers_running'] }} {{ __('running') }}</div>
        </div>
    </div>
</x-admin>
