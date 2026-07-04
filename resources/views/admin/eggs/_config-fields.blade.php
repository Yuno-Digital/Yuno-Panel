@php
    $ta = 'mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $row = 'block border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm';

    $initialImages = collect($egg->docker_images ?? [])
        ->map(fn ($img, $name) => ['name' => $name === $img ? '' : (string) $name, 'image' => (string) $img])
        ->values()->all();
    if ($initialImages === []) {
        $initialImages = [['name' => '', 'image' => '']];
    }

    $initialStartups = collect($egg->startup_commands ?? [])
        ->map(fn ($c) => is_array($c)
            ? ['name' => (string) ($c['name'] ?? ''), 'command' => (string) ($c['command'] ?? '')]
            : ['name' => '', 'command' => (string) $c])
        ->values()->all();
    if ($initialStartups === [] && $egg->startup) {
        $initialStartups = [['name' => '', 'command' => (string) $egg->startup]];
    }
    if ($initialStartups === []) {
        $initialStartups = [['name' => '', 'command' => '']];
    }
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
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="{{ $ta }}">{{ old('description', $egg->description) }}</textarea>
    </div>

    {{-- Icon: a URL or an uploaded image stored as a data:image value --}}
    <div x-data="{ icon: {{ Illuminate\Support\Js::from(old('icon', $egg->icon)) }} }">
        <x-input-label :value="__('Icon')" />
        <p class="mt-1 mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Optional. A URL, or upload a small image (stored inline as data:image, max ~256 KB).') }}</p>
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 shrink-0 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden">
                <template x-if="icon"><img :src="icon" alt="" class="w-full h-full object-contain"></template>
                <template x-if="!icon"><span class="text-xs text-gray-400">—</span></template>
            </div>
            <div class="flex-1 space-y-2">
                <input type="text" name="icon" x-model="icon" placeholder="https://…  {{ __('or') }}  data:image/…" class="{{ $row }} w-full font-mono">
                <div class="flex items-center gap-3">
                    <input type="file" accept="image/*"
                           @change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = () => icon = r.result; r.readAsDataURL(f); }"
                           class="text-xs text-gray-600 dark:text-gray-400 file:mr-2 file:rounded-md file:border-0 file:bg-gray-200 dark:file:bg-gray-700 file:px-3 file:py-1 file:text-gray-700 dark:file:text-gray-200">
                    <button type="button" x-show="icon" @click="icon = ''" class="text-xs font-medium text-red-600 hover:underline">{{ __('Remove') }}</button>
                </div>
            </div>
        </div>
        <x-input-error :messages="$errors->get('icon')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="tags" :value="__('Tags')" />
            <textarea id="tags" name="tags" rows="2" class="{{ $ta }}" placeholder="minecraft, java">{{ old('tags', implode(', ', $egg->tags ?? [])) }}</textarea>
        </div>
        <div>
            <x-input-label for="features" :value="__('Features')" />
            <textarea id="features" name="features" rows="2" class="{{ $ta }}" placeholder="eula&#10;java_version">{{ old('features', implode("\n", $egg->features ?? [])) }}</textarea>
        </div>
    </div>

    {{-- Docker images: add as many as you like, selectable at server creation --}}
    <div x-data="{ images: {{ Illuminate\Support\Js::from($initialImages) }} }">
        <x-input-label :value="__('Docker images')" />
        <p class="mt-1 mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Add one or more images. The first is the default; users pick one when creating a server.') }}</p>
        <div class="space-y-2">
            <template x-for="(img, i) in images" :key="i">
                <div class="flex items-center gap-2">
                    <input type="text" name="docker_image_names[]" x-model="images[i].name" placeholder="{{ __('Display name (optional)') }}" class="{{ $row }} w-44 shrink-0">
                    <input type="text" name="docker_image_values[]" x-model="images[i].image" placeholder="ghcr.io/pelican-eggs/yolks:java_21" class="{{ $row }} flex-1 min-w-0 font-mono">
                    <button type="button" @click="images.splice(i, 1)" x-show="images.length > 1"
                            class="shrink-0 w-9 h-9 rounded-md text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('Remove') }}">&times;</button>
                </div>
            </template>
        </div>
        <button type="button" @click="images.push({ name: '', image: '' })"
                class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
            + {{ __('Add image') }}
        </button>
        <x-input-error :messages="$errors->get('docker_image_values')" class="mt-2" />
    </div>

    {{-- Startup commands: add several, the first is the default --}}
    <div x-data="{ cmds: {{ Illuminate\Support\Js::from($initialStartups) }} }">
        <x-input-label :value="__('Startup commands')" />
        <p class="mt-1 mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Add one or more start commands. The first is the default.') }}</p>
        <div class="space-y-3">
            <template x-for="(cmd, i) in cmds" :key="i">
                <div class="rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs font-medium text-gray-400 w-6" x-text="'#' + (i + 1)"></span>
                        <input type="text" name="startup_command_names[]" x-model="cmds[i].name" placeholder="{{ __('Label (optional)') }}" class="{{ $row }} flex-1 min-w-0">
                        <button type="button" @click="cmds.splice(i, 1)" x-show="cmds.length > 1"
                                class="shrink-0 w-8 h-8 rounded-md text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('Remove') }}">&times;</button>
                    </div>
                    <textarea name="startup_command_values[]" x-model="cmds[i].command" rows="2" spellcheck="false"
                              placeholder="java -Xmx@{{SERVER_MEMORY}}M -jar server.jar" class="{{ $row }} mt-2 block w-full font-mono"></textarea>
                </div>
            </template>
        </div>
        <button type="button" @click="cmds.push({ name: '', command: '' })"
                class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
            + {{ __('Add command') }}
        </button>
        <x-input-error :messages="$errors->get('startup_command_values')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="file_denylist" :value="__('File denylist')" />
            <textarea id="file_denylist" name="file_denylist" rows="2" class="{{ $ta }} font-mono text-sm" placeholder="server.properties">{{ old('file_denylist', implode("\n", $egg->file_denylist ?? [])) }}</textarea>
        </div>
        <div>
            <x-input-label for="update_url" :value="__('Update URL')" />
            <x-text-input id="update_url" name="update_url" type="url" class="mt-1 block w-full font-mono text-sm" :value="old('update_url', $egg->update_url)" placeholder="https://.../egg.json" />
        </div>
    </div>

    <div>
        <x-input-label for="config_from" :value="__('Copy settings from')" />
        <select id="config_from" name="config_from" class="{{ $ta }}">
            <option value="">{{ __('— None —') }}</option>
            @foreach ($eggs as $other)
                <option value="{{ $other->id }}" @selected(old('config_from', $egg->config_from) == $other->id)>{{ $other->name }}</option>
            @endforeach
        </select>
    </div>
</div>
