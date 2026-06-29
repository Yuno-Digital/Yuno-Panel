<x-admin title="Nodes">
    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('New node') }}</h3>

    <div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.nodes.store') }}">
            @include('admin.nodes._form')
        </form>
    </div>
</x-admin>
