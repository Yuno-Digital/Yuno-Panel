<x-admin title="Users">
    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit user') }}: {{ $user->name }}</h3>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @method('PUT')
            @include('admin.users._form')
        </form>
    </div>
</x-admin>
