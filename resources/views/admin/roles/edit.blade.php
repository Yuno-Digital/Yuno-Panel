<x-admin title="Roles">
    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit role') }}</h3>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @method('PUT')
            @include('admin.roles._form')
        </form>
    </div>
</x-admin>
