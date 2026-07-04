<x-admin title="Plugins">
    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('admin.plugins.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">&larr; {{ __('Plugins') }}</a>
        <span class="text-gray-300 dark:text-gray-600">/</span>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $plugin['name'] }} — {{ __('Settings') }}</h3>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin['id']) }}" class="space-y-5">
            @csrf @method('PUT')
            @foreach ($plugin['settings'] as $field)
                @php
                    $key = $field['key'] ?? null;
                    $type = $field['type'] ?? 'text';
                    $current = old('settings.'.$key, $values[$key] ?? ($field['default'] ?? ''));
                @endphp
                @continue(! $key)
                <div>
                    <x-input-label :for="'set_'.$key" :value="$field['label'] ?? $key" />
                    @if ($type === 'textarea')
                        <textarea id="set_{{ $key }}" name="settings[{{ $key }}]" rows="3"
                                  class="mt-1 block w-full font-mono text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">{{ $current }}</textarea>
                    @elseif ($type === 'boolean')
                        <label class="mt-1 inline-flex items-center gap-2">
                            <input type="hidden" name="settings[{{ $key }}]" value="0">
                            <input type="checkbox" name="settings[{{ $key }}]" value="1" @checked($current)
                                   class="rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-indigo-600 dark:checked:bg-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Enabled') }}</span>
                        </label>
                    @else
                        <x-text-input :id="'set_'.$key" :name="'settings['.$key.']'"
                                      :type="$type === 'password' ? 'password' : 'text'"
                                      class="mt-1 block w-full font-mono text-sm" :value="$current"
                                      autocomplete="off" />
                    @endif
                    @if (! empty($field['description']))
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $field['description'] }}</p>
                    @endif
                </div>
            @endforeach

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                <a href="{{ route('admin.plugins.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin>
