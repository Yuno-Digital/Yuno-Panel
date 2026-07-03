@php
    $inp = 'mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm';
@endphp

<div class="space-y-4">
    {{-- Existing variables --}}
    @forelse ($egg->variables as $variable)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <form method="POST" action="{{ route('admin.eggs.variables.update', [$egg, $variable]) }}" class="space-y-4">
                @csrf @method('PUT')
                @include('admin.eggs._variable-fields', ['var' => $variable, 'inp' => $inp])
                <x-primary-button>{{ __('Save variable') }}</x-primary-button>
            </form>
            <form method="POST" action="{{ route('admin.eggs.variables.destroy', [$egg, $variable]) }}"
                  class="mt-2" data-confirm="Delete this variable?" data-confirm-button="Delete">
                @csrf @method('DELETE')
                <button class="text-sm text-red-600 hover:underline">{{ __('Delete variable') }}</button>
            </form>
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No variables yet. Add one below.') }}</p>
    @endforelse

    {{-- Add new --}}
    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-4">
        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('Add variable') }}</h4>
        <form method="POST" action="{{ route('admin.eggs.variables.store', $egg) }}" class="space-y-4">
            @csrf
            @include('admin.eggs._variable-fields', ['var' => new \App\Models\EggVariable(['user_viewable' => true, 'user_editable' => true, 'rules' => 'nullable|string']), 'inp' => $inp])
            <x-primary-button>{{ __('Add variable') }}</x-primary-button>
        </form>
    </div>
</div>
