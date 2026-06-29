<x-admin title="Eggs">
    <div x-data="{ tab: 'config' }">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('New egg') }}</h3>

        @include('admin.eggs._tabnav', ['tabs' => [
            'config' => __('Configuration'),
            'process' => __('Process Management'),
            'script' => __('Install Script'),
        ]])

        <form method="POST" action="{{ route('admin.eggs.store') }}">
            @csrf
            <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl p-6 max-w-4xl">
                <div x-show="tab === 'config'">@include('admin.eggs._config-fields')</div>
                <div x-show="tab === 'process'" x-cloak>@include('admin.eggs._process-fields')</div>
                <div x-show="tab === 'script'" x-cloak>@include('admin.eggs._script-fields')</div>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-primary-button>{{ __('Create egg') }}</x-primary-button>
                <a href="{{ route('admin.eggs.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Variables can be added after creating the egg.') }}</p>
        </form>
    </div>
</x-admin>
