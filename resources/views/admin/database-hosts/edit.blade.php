<x-admin title="Database Hosts">
    <div class="mb-4">
        <a href="{{ route('admin.database-hosts.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">&larr; {{ __('Database Hosts') }}</a>
        <h3 class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit database host') }}: {{ $host->name }}</h3>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.database-hosts.update', $host) }}">
            @method('PUT')
            @include('admin.database-hosts._form')
        </form>
    </div>
</x-admin>
