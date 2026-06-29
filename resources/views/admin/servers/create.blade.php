<x-admin title="Servers">
    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('New server') }}</h3>

    <div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.servers.store') }}">
            @include('admin.servers._form')
        </form>
    </div>
</x-admin>
