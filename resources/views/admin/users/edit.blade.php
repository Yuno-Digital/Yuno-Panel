<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Admin') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.subnav')
            @include('admin.partials.flash')

            <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Edit user') }}: {{ $user->name }}</h3>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @method('PUT')
                    @include('admin.users._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
