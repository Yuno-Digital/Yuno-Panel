@php
    $ta = 'mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $mono = $ta.' font-mono text-sm';
    $dockerText = old('docker_images', collect($egg->docker_images ?? [])->map(fn ($img, $name) => $name === $img ? $img : $name.'|'.$img)->implode("\n"));
@endphp
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $egg->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="author" :value="__('Author')" />
            <x-text-input id="author" name="author" type="text" class="mt-1 block w-full" :value="old('author', $egg->author)" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('author')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="{{ $ta }}">{{ old('description', $egg->description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="tags" :value="__('Tags')" />
            <textarea id="tags" name="tags" rows="2" class="{{ $ta }}" placeholder="minecraft, java">{{ old('tags', implode(', ', $egg->tags ?? [])) }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Comma or newline separated.') }}</p>
        </div>
        <div>
            <x-input-label for="features" :value="__('Features')" />
            <textarea id="features" name="features" rows="2" class="{{ $ta }}" placeholder="eula&#10;java_version">{{ old('features', implode("\n", $egg->features ?? [])) }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('One per line.') }}</p>
        </div>
    </div>

    <div>
        <x-input-label for="docker_images" :value="__('Docker images')" />
        <textarea id="docker_images" name="docker_images" rows="3" class="{{ $mono }}" required placeholder="Java 21|ghcr.io/pelican-eggs/yolks:java_21">{{ $dockerText }}</textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('One per line as "Display name|image:tag" (or just the image).') }}</p>
        <x-input-error :messages="$errors->get('docker_images')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="file_denylist" :value="__('File denylist')" />
            <textarea id="file_denylist" name="file_denylist" rows="2" class="{{ $mono }}" placeholder="server.properties">{{ old('file_denylist', implode("\n", $egg->file_denylist ?? [])) }}</textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Files users may not edit. One per line.') }}</p>
        </div>
        <div>
            <x-input-label for="update_url" :value="__('Update URL')" />
            <x-text-input id="update_url" name="update_url" type="url" class="mt-1 block w-full font-mono text-sm" :value="old('update_url', $egg->update_url)" placeholder="https://.../egg.json" />
            <x-input-error :messages="$errors->get('update_url')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="startup" :value="__('Startup command')" />
        <textarea id="startup" name="startup" rows="2" class="{{ $mono }}" required placeholder="java -Xms128M -Xmx@{{SERVER_MEMORY}}M -jar server.jar">{{ old('startup', $egg->startup) }}</textarea>
        <x-input-error :messages="$errors->get('startup')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="config_from" :value="__('Copy settings from')" />
        <select id="config_from" name="config_from" class="{{ $ta }}">
            <option value="">{{ __('— None —') }}</option>
            @foreach ($eggs as $other)
                <option value="{{ $other->id }}" @selected(old('config_from', $egg->config_from) == $other->id)>{{ $other->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Inherit process configuration from another egg.') }}</p>
    </div>
</div>
