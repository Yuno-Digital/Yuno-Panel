<x-admin title="Eggs">
    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Edit egg') }}: {{ $egg->name }}</h3>

    <div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.eggs.update', $egg) }}">
            @method('PUT')
            @include('admin.eggs._form')
        </form>
    </div>
</x-admin>
