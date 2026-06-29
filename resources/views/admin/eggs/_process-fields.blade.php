@php
    $ta = 'mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $mono = $ta.' font-mono text-sm';
@endphp
<div class="space-y-6">
    <div>
        <x-input-label for="config_stop" :value="__('Stop command')" />
        <x-text-input id="config_stop" name="config_stop" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('config_stop', $egg->config_stop)" placeholder="stop" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Command (or ^C) sent to gracefully stop the server.') }}</p>
        <x-input-error :messages="$errors->get('config_stop')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="config_startup" :value="__('Start configuration  { }')" />
        <textarea id="config_startup" name="config_startup" rows="5" class="{{ $mono }}" placeholder='{ "done": ")! For help, type " }'>{{ old('config_startup', $egg->config_startup) }}</textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('JSON describing how to detect that the server has finished booting.') }}</p>
        <x-input-error :messages="$errors->get('config_startup')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="config_files" :value="__('Configuration files  { }')" />
        <textarea id="config_files" name="config_files" rows="6" class="{{ $mono }}" placeholder='{ "server.properties": { "parser": "properties", "find": {} } }'>{{ old('config_files', $egg->config_files) }}</textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('JSON map of files the daemon rewrites before boot.') }}</p>
        <x-input-error :messages="$errors->get('config_files')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="config_logs" :value="__('Log configuration  { }')" />
        <textarea id="config_logs" name="config_logs" rows="4" class="{{ $mono }}" placeholder='{ "custom": false, "location": "logs/latest.log" }'>{{ old('config_logs', $egg->config_logs) }}</textarea>
        <x-input-error :messages="$errors->get('config_logs')" class="mt-2" />
    </div>
</div>
