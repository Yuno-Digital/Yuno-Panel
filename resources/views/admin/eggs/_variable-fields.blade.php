<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label :value="__('Name')" />
        <input type="text" name="name" value="{{ old('name', $var->name) }}" class="{{ $inp }}" required>
    </div>
    <div>
        <x-input-label :value="__('Environment variable')" />
        <input type="text" name="env_variable" value="{{ old('env_variable', $var->env_variable) }}"
               class="{{ $inp }} font-mono" placeholder="SERVER_JARFILE" required>
        @php $ref = '{{'.($var->env_variable ?: 'NAME').'}}'; @endphp
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Referenced in startup as') }}
            <code class="text-gray-700 dark:text-gray-300">{{ $ref }}</code>
        </p>
    </div>
</div>

<div>
    <x-input-label :value="__('Description')" />
    <textarea name="description" rows="2" class="{{ $inp }}">{{ old('description', $var->description) }}</textarea>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label :value="__('Default value')" />
        <input type="text" name="default_value" value="{{ old('default_value', $var->default_value) }}" class="{{ $inp }} font-mono">
    </div>
    <div>
        <x-input-label :value="__('Validation rules')" />
        <input type="text" name="rules" value="{{ old('rules', $var->rules ?: 'nullable|string') }}" class="{{ $inp }} font-mono" required>
    </div>
</div>

<div class="flex flex-wrap gap-6">
    <label class="inline-flex items-center">
        <input type="hidden" name="user_viewable" value="0">
        <input type="checkbox" name="user_viewable" value="1"
               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
               @checked(old('user_viewable', $var->user_viewable ?? true))>
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('User viewable') }}</span>
    </label>
    <label class="inline-flex items-center">
        <input type="hidden" name="user_editable" value="0">
        <input type="checkbox" name="user_editable" value="1"
               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
               @checked(old('user_editable', $var->user_editable ?? true))>
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('User editable') }}</span>
    </label>
</div>
