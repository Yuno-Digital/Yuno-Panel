<x-admin title="Users">
    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('New user') }}</h3>

    <div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @include('admin.users._form')
        </form>
    </div>
</x-admin>
