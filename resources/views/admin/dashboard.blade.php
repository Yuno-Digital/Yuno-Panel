<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Admin') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.subnav')
            @include('admin.partials.flash')

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
        </div>
    </div>
</x-app-layout>
