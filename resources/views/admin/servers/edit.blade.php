<x-admin title="Servers">
    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Edit server') }}: {{ $server->name }}</h3>

    <div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.servers.update', $server) }}">
            @method('PUT')
            @include('admin.servers._form')
        </form>
    </div>
</x-admin>
