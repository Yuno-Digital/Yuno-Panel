<x-admin title="Eggs">
    <div x-data="{ tab: 'config' }">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit egg') }}: {{ $egg->name }}</h3>
            <span class="font-mono text-xs text-gray-400">{{ $egg->uuid }}</span>
        </div>

        @include('admin.eggs._tabnav', ['tabs' => [
            'config' => __('Configuration'),
            'process' => __('Process Management'),
            'script' => __('Install Script'),
            'variables' => __('Variables').' ('.$egg->variables->count().')',
        ]])

        {{-- Main egg form: configuration / process / script --}}
        <form method="POST" action="{{ route('admin.eggs.update', $egg) }}" x-show="tab !== 'variables'">
            @csrf @method('PUT')
            <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl p-6 max-w-4xl">
                <div x-show="tab === 'config'">@include('admin.eggs._config-fields')</div>
                <div x-show="tab === 'process'" x-cloak>@include('admin.eggs._process-fields')</div>
                <div x-show="tab === 'script'" x-cloak>@include('admin.eggs._script-fields')</div>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-primary-button>{{ __('Save egg') }}</x-primary-button>
                <a href="{{ route('admin.eggs.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Back') }}</a>
            </div>
        </form>

        {{-- Variables management lives outside the main form (separate forms) --}}
        <div x-show="tab === 'variables'" x-cloak>
            <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl p-6 max-w-4xl">
                @include('admin.eggs._variables')
            </div>
        </div>
    </div>
</x-admin>
