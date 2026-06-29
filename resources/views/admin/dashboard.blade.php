<x-admin title="Overview">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Users') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['users'] }}</div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['admins'] }} {{ __('admins') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nodes') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['nodes'] }}</div>
            <div class="mt-1 text-xs text-green-600">{{ $stats['nodes_online'] }} {{ __('online') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Servers') }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['servers'] }}</div>
            <div class="mt-1 text-xs text-green-600">{{ $stats['servers_running'] }} {{ __('running') }}</div>
        </div>
    </div>
</x-admin>
