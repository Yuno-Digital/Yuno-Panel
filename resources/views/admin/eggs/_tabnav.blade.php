{{-- Expects $tabs = ['key' => 'Label', ...] and an ancestor x-data with `tab`. --}}
<div class="mb-6 overflow-x-auto">
    <nav class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 p-1.5 ring-1 ring-gray-200 dark:ring-gray-700">
        @foreach ($tabs as $key => $label)
            <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm ring-1 ring-black/5'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                    class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold tracking-tight transition-all duration-150">
                {{ $label }}
            </button>
        @endforeach
    </nav>
</div>
