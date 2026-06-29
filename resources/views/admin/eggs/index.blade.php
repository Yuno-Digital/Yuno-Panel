<x-admin title="Eggs">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">{{ __('Eggs') }}</h3>
        <a href="{{ route('admin.eggs.create') }}">
            <x-primary-button>{{ __('New egg') }}</x-primary-button>
        </a>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
        @if ($eggs->isEmpty())
            <div class="p-6 text-gray-500">{{ __('No eggs yet. Eggs are reusable server templates (image + startup command).') }}</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">{{ __('Name') }}</th>
                        <th class="px-6 py-3">{{ __('Author') }}</th>
                        <th class="px-6 py-3">{{ __('Docker image') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                    @foreach ($eggs as $egg)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $egg->name }}</td>
                            <td class="px-6 py-4">{{ $egg->author ?? '—' }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $egg->docker_image }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.eggs.edit', $egg) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.eggs.destroy', $egg) }}"
                                          onsubmit="return confirm('Delete this egg?');">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $eggs->links() }}</div>
</x-admin>
