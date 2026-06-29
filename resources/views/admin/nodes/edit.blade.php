<x-admin title="Nodes">
    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit node') }}: {{ $node->name }}</h3>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.nodes.update', $node) }}">
            @method('PUT')
            @include('admin.nodes._form')
        </form>
    </div>
</x-admin>
