@php
    $ta = 'mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
@endphp
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="sm:col-span-2">
            <x-input-label for="script_container" :value="__('Install container')" />
            <x-text-input id="script_container" name="script_container" type="text" class="mt-1 block w-full font-mono text-sm"
                          :value="old('script_container', $egg->script_container ?? 'ghcr.io/pelican-eggs/installers:debian')" required />
            <x-input-error :messages="$errors->get('script_container')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="script_entry" :value="__('Script entry')" />
            <x-text-input id="script_entry" name="script_entry" type="text" class="mt-1 block w-full font-mono text-sm"
                          :value="old('script_entry', $egg->script_entry ?? 'bash')" required />
            <x-input-error :messages="$errors->get('script_entry')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="copy_script_from" :value="__('Copy script from')" />
        <select id="copy_script_from" name="copy_script_from" class="{{ $ta }}">
            <option value="">{{ __('— None —') }}</option>
            @foreach ($eggs as $other)
                <option value="{{ $other->id }}" @selected(old('copy_script_from', $egg->copy_script_from) == $other->id)>{{ $other->name }}</option>
            @endforeach
        </select>
    </div>

    <label class="inline-flex items-center">
        <input type="hidden" name="script_is_privileged" value="0">
        <input type="checkbox" name="script_is_privileged" value="1"
               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
               @checked(old('script_is_privileged', $egg->script_is_privileged ?? true))>
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Run install script with elevated (privileged) permissions') }}</span>
    </label>

    <div>
        <x-input-label for="script_install" :value="__('Install script')" />
        <textarea id="script_install" name="script_install" rows="16"
                  class="mt-1 block w-full font-mono text-xs leading-relaxed border-gray-300 dark:border-gray-600 bg-gray-900 text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  spellcheck="false" placeholder="#!/bin/bash&#10;apt update&#10;...">{{ old('script_install', $egg->script_install) }}</textarea>
        <x-input-error :messages="$errors->get('script_install')" class="mt-2" />
    </div>
</div>
