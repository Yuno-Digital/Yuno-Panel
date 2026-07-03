<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ $server->name }}</h2>
            <span class="text-xs font-mono text-gray-400">{{ $server->allocation?->address() }}</span>
        </div>
    </x-slot>

    @php
        $editable = $server->egg ? $server->egg->variables->where('user_editable', true) : collect();
        $values = $server->variables->mapWithKeys(fn ($sv) => [optional($sv->eggVariable)->env_variable => $sv->variable_value]);

        // Startup command presets and docker images offered by the egg.
        $startupPresets = collect($server->egg?->startup_commands ?? [])
            ->map(fn ($c) => is_array($c)
                ? ['name' => $c['name'] ?? '', 'command' => (string) ($c['command'] ?? '')]
                : ['name' => '', 'command' => (string) $c])
            ->filter(fn ($c) => $c['command'] !== '')->values();
        $dockerImages = collect($server->egg?->docker_images ?? [])
            ->map(fn ($ref, $label) => ['label' => is_string($label) && $label !== '' ? $label : $ref, 'ref' => $ref])
            ->values();
        $editableMeta = $editable->map(fn ($v) => [
            'env_variable' => $v->env_variable,
            'name' => $v->name,
            'description' => $v->description,
            'default_value' => $v->default_value,
        ])->values();
    @endphp

    <div class="py-10" x-data="serverConsole({
            wsInfoUrl: '{{ route('servers.ws', $server) }}',
            powerUrl: '{{ route('servers.power', $server) }}',
            csrf: '{{ csrf_token() }}',
            basePath: '{{ url('servers/'.$server->id) }}',
            tab: '{{ $activeTab }}'
         })" x-init="init()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-200">{{ session('error') }}</div>
            @endif

            {{-- Status + power --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 flex flex-wrap items-center gap-4">
                <span class="inline-flex items-center gap-2 text-sm font-semibold">
                    <span class="w-2.5 h-2.5 rounded-full" :class="stateColor"></span>
                    <span class="text-gray-700 dark:text-gray-200" x-text="stateLabel"></span>
                </span>
                <span x-show="stats.state === 'missing'" x-cloak class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Not installed yet — use Settings → Reinstall to create the server.') }}
                </span>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    CPU <span class="font-medium text-gray-800 dark:text-gray-200" x-text="(stats.cpu_percent ?? 0).toFixed(1) + '%'"></span>
                    · RAM <span class="font-medium text-gray-800 dark:text-gray-200" x-text="Math.round(stats.memory_mb ?? 0) + ' / ' + Math.round(stats.memory_limit_mb ?? {{ $server->memory_mb }}) + ' MB'"></span>
                </div>
                <div class="ms-auto flex items-center gap-2">
                    <button @click="power('start')" :disabled="!canStart"
                            class="px-3 py-1.5 rounded-md text-sm font-medium bg-green-600 text-white hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-green-600">{{ __('Start') }}</button>
                    <button @click="power('restart')" :disabled="!canRestart"
                            class="px-3 py-1.5 rounded-md text-sm font-medium bg-amber-500 text-white hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-amber-500">{{ __('Restart') }}</button>
                    <button @click="power('stop')" :disabled="!canStop"
                            class="px-3 py-1.5 rounded-md text-sm font-medium bg-red-600 text-white hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-red-600">{{ __('Stop') }}</button>
                </div>
            </div>

            {{-- Tabs --}}
            <nav class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 p-1.5 ring-1 ring-gray-200 dark:ring-gray-700">
                @foreach (['console' => __('Console'), 'files' => __('Files'), 'startup' => __('Startup'), 'settings' => __('Settings')] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                            class="rounded-lg px-4 py-2 text-sm font-semibold">{{ $label }}</button>
                @endforeach
            </nav>

            {{-- Console --}}
            <div x-show="tab === 'console'">
                <pre x-ref="console" class="h-96 overflow-auto rounded-lg bg-gray-900 text-gray-100 text-xs font-mono p-4 whitespace-pre-wrap" x-text="logs || '{{ __('Waiting for output…') }}'"></pre>
                <form @submit.prevent="sendCommand()" class="mt-2 flex gap-2">
                    <span class="hidden sm:flex items-center text-gray-400 font-mono text-sm px-2">&gt;</span>
                    <input x-model="commandInput" type="text" autocomplete="off" spellcheck="false"
                           :placeholder="stats.state === 'running' ? '{{ __('Type a command and press Enter…') }}' : '{{ __('Server is not running') }}'"
                           :disabled="stats.state !== 'running'"
                           class="flex-1 font-mono text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 disabled:opacity-50">
                    <button type="submit" :disabled="stats.state !== 'running'"
                            class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">{{ __('Send') }}</button>
                </form>
            </div>

            {{-- Files --}}
            <div x-show="tab === 'files'" x-cloak
                 x-data="fileManager({ base: '{{ route('servers.files', $server) }}', readUrl: '{{ route('servers.files.read', $server) }}', writeUrl: '{{ route('servers.files.write', $server) }}', deleteUrl: '{{ route('servers.files.delete', $server) }}', csrf: '{{ csrf_token() }}' })"
                 x-init="load()">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    {{-- Toolbar: breadcrumb + search --}}
                    <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400 p-4 border-b border-gray-100 dark:border-gray-700">
                        <button @click="up()" x-show="path !== '/'" class="hover:underline shrink-0">⬑ {{ __('up') }}</button>
                        <span class="font-mono truncate" x-text="path"></span>
                        <input x-model="search" type="search" autocomplete="off"
                               placeholder="{{ __('Search files…') }}"
                               class="ms-auto w-48 max-w-[45%] text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Bulk actions when something is selected --}}
                    <div x-show="selected.length" x-cloak
                         class="flex items-center gap-3 px-4 py-2 text-sm bg-indigo-50 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-900/40">
                        <span class="text-gray-600 dark:text-gray-300"><span x-text="selected.length"></span> {{ __('selected') }}</span>
                        <button @click="deleteSelected()" class="ms-auto inline-flex items-center gap-1 font-medium text-red-600 hover:text-red-700">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            {{ __('Delete') }}
                        </button>
                    </div>

                    {{-- File table --}}
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                                <th class="w-10 px-4 py-2"><input type="checkbox" @change="toggleAll()" :checked="allSelected"
                                        class="rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-indigo-600 dark:checked:bg-indigo-500 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"></th>
                                <th class="px-2 py-2 font-medium">{{ __('Name') }}</th>
                                <th class="px-2 py-2 font-medium text-right w-28">{{ __('Size') }}</th>
                                <th class="px-2 py-2 font-medium w-44 hidden sm:table-cell">{{ __('Modified') }}</th>
                                <th class="px-4 py-2 w-28"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="e in filtered" :key="e.name">
                                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-2"><input type="checkbox" :value="e.name" x-model="selected"
                                            class="rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-indigo-600 dark:checked:bg-indigo-500 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"></td>
                                    <td class="px-2 py-2 min-w-0">
                                        <button @click="open(e)" class="flex items-center gap-2 text-gray-800 dark:text-gray-200 hover:text-indigo-600 max-w-full">
                                            <span class="shrink-0" x-text="fileIcon(e)"></span>
                                            <span class="truncate" x-text="e.name"></span>
                                        </button>
                                    </td>
                                    <td class="px-2 py-2 text-right text-gray-400 dark:text-gray-500 whitespace-nowrap" x-text="e.directory ? '—' : fmtSize(e.size)"></td>
                                    <td class="px-2 py-2 text-gray-400 dark:text-gray-500 whitespace-nowrap hidden sm:table-cell" x-text="fmtDate(e.modified)"></td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center justify-end gap-0.5 text-gray-400">
                                            {{-- Open / view --}}
                                            <button @click="open(e)" title="{{ __('Open') }}" class="p-1.5 rounded hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </button>
                                            {{-- Three-dot menu --}}
                                            <div class="relative" x-data="{ menu: false }" @click.outside="menu = false">
                                                <button @click="menu = !menu" title="{{ __('More') }}" class="p-1.5 rounded hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7a1.25 1.25 0 100-2.5A1.25 1.25 0 0012 7zM12 13.25a1.25 1.25 0 100-2.5 1.25 1.25 0 000 2.5zM12 19.5a1.25 1.25 0 100-2.5 1.25 1.25 0 000 2.5z"/></svg>
                                                </button>
                                                <div x-show="menu" x-cloak @click="menu = false"
                                                     class="absolute right-0 z-10 mt-1 w-36 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 py-1 text-sm text-gray-700 dark:text-gray-200">
                                                    <button @click="open(e)" class="block w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700" x-text="e.directory ? '{{ __('Open') }}' : '{{ __('Edit') }}'"></button>
                                                    <button @click="deleteEntry(e)" class="block w-full text-left px-3 py-1.5 text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('Delete') }}</button>
                                                </div>
                                            </div>
                                            {{-- Trash --}}
                                            <button @click="deleteEntry(e)" title="{{ __('Delete') }}" class="p-1.5 rounded hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filtered.length === 0">
                                <td colspan="5" class="px-4 py-4 text-gray-500 dark:text-gray-400"
                                    x-text="search ? '{{ __('No files match your search.') }}' : '{{ __('Empty or unreachable.') }}'"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- File editor modal (Monaco — the VS Code editor — with a textarea fallback) --}}
                <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     @keydown.escape.window="closeEditor()">
                    <div class="absolute inset-0 bg-black/50" @click="closeEditor()"></div>
                    <div class="relative w-full max-w-5xl h-[85vh] flex flex-col rounded-lg bg-white dark:bg-gray-800 shadow-xl overflow-hidden">
                        <div class="flex items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                            <span class="font-mono text-sm text-gray-700 dark:text-gray-300 truncate" x-text="editing"></span>
                            <div class="flex items-center gap-2 shrink-0">
                                <button @click="save()" class="px-3 py-1.5 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">{{ __('Save') }}</button>
                                <button @click="closeEditor()" class="px-3 py-1.5 rounded-md text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">{{ __('Close') }}</button>
                            </div>
                        </div>
                        <div class="flex-1 min-h-0 relative">
                            <div x-ref="editor" x-show="monacoReady" class="absolute inset-0"></div>
                            <textarea x-show="!monacoReady" x-model="contents" spellcheck="false"
                                      class="absolute inset-0 w-full h-full font-mono text-xs border-0 bg-gray-900 text-gray-100 p-4 resize-none focus:ring-0"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Startup --}}
            <div x-show="tab === 'startup'" x-cloak class="space-y-6"
                 x-data="startupEditor({
                     presets: @js($startupPresets),
                     images: @js($dockerImages),
                     variables: @js($editableMeta),
                     current: {
                         startup: @js($server->startup ?? ''),
                         dockerImage: @js($server->docker_image ?? ''),
                         values: @js($values),
                         memory: {{ (int) $server->memory_mb }},
                         port: {{ (int) ($server->allocation?->port ?? 0) }},
                         ip: @js($server->allocation?->ip ?? '0.0.0.0'),
                     },
                 })">
                <form method="POST" action="{{ route('servers.update', $server) }}" class="space-y-6">
                    @csrf @method('PATCH')

                    {{-- Startup command + docker image selectors --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 grid gap-x-10 gap-y-6 sm:grid-cols-2">
                        <div>
                            <x-input-label :value="__('Startup command')" />
                            <select name="startup" x-model="startup"
                                    class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <template x-for="(p, i) in presetOptions" :key="i">
                                    <option :value="p.command" x-text="p.name || 'Default'"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Choose one of the egg\'s startup commands.') }}</p>
                        </div>
                        <div x-show="imageOptions.length">
                            <x-input-label :value="__('Docker image')" />
                            <select name="docker_image" x-model="dockerImage"
                                    class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <template x-for="(img, i) in imageOptions" :key="i">
                                    <option :value="img.ref" x-text="img.label"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('The container image this server runs in.') }}</p>
                        </div>
                    </div>

                    {{-- Live preview with variables substituted --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Preview') }}</h3>
                            <button type="button" @click="showPreview = !showPreview"
                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                                    x-text="showPreview ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('The startup command with the variables below resolved.') }}</p>
                        <pre x-show="showPreview" x-text="preview"
                             class="mt-3 overflow-auto rounded-lg bg-gray-900 text-gray-100 text-xs font-mono p-4 whitespace-pre-wrap"></pre>
                    </div>

                    {{-- Editable variables --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Startup variables') }}</h3>
                        <template x-if="variables.length === 0">
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('There are no editable settings for this server.') }}</p>
                        </template>
                        <div class="mt-5 space-y-5">
                            <template x-for="v in variables" :key="v.env_variable">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" x-text="v.name"></label>
                                    <input type="text" x-model="values[v.env_variable]" :name="'variables[' + v.env_variable + ']'"
                                           class="mt-1 block w-full font-mono text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <p x-show="v.description" x-text="v.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </form>
            </div>

            {{-- Settings --}}
            <div x-show="tab === 'settings'" x-cloak class="space-y-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                        @foreach ([__('Egg') => $server->egg?->name ?? '—', __('Node') => $server->node?->name ?? '—', __('Address') => $server->allocation?->address() ?? '—', __('Memory') => \App\Support\Format::size($server->memory_mb), __('Disk') => \App\Support\Format::size($server->disk_mb), __('CPU') => $server->cpu ? $server->cpu.' %' : __('unlimited')] as $label => $value)
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                                <dd class="mt-0.5 font-medium text-gray-900 dark:text-gray-100">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Reinstall --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border border-red-200 dark:border-red-900/50">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Reinstall server') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Re-runs the egg install script (image pull + setup). Your files are kept. Progress is shown live in the console.') }}</p>
                    <form method="POST" action="{{ route('servers.install', $server) }}" class="mt-4"
                          data-confirm="Reinstall the container? Your files are kept, but the install script will run again." data-confirm-button="Reinstall" data-confirm-icon="warning">
                        @csrf
                        <x-danger-button type="submit" x-bind:disabled="stats.state === 'unreachable'">{{ __('Reinstall') }}</x-danger-button>
                    </form>
                </div>
            </div>

            <a href="{{ route('servers.index') }}" class="inline-block text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; {{ __('Back to servers') }}</a>
        </div>
    </div>

    <script>
        // Lazily load the Monaco editor (the engine behind VS Code) from a CDN,
        // reusing a single load across the page. Resolves with window.monaco.
        function loadMonaco() {
            if (window.monaco) return Promise.resolve(window.monaco);
            if (window.__monacoLoading) return window.__monacoLoading;

            const base = 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs';
            window.__monacoLoading = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = base + '/loader.js';
                script.onload = () => {
                    window.require.config({ paths: { vs: base } });
                    window.require(['vs/editor/editor.main'], () => resolve(window.monaco));
                };
                script.onerror = () => reject(new Error('failed to load Monaco'));
                document.head.appendChild(script);
            });
            return window.__monacoLoading;
        }

        // Guess a Monaco language id from a file path.
        function monacoLanguage(path) {
            const name = (path.split('/').pop() || '').toLowerCase();
            if (name === 'dockerfile') return 'dockerfile';
            const ext = name.includes('.') ? name.split('.').pop() : '';
            const map = {
                json: 'json', js: 'javascript', mjs: 'javascript', cjs: 'javascript', ts: 'typescript',
                yml: 'yaml', yaml: 'yaml', toml: 'ini', ini: 'ini', conf: 'ini', cfg: 'ini', env: 'ini', properties: 'ini',
                php: 'php', py: 'python', rb: 'ruby', go: 'go', rs: 'rust', java: 'java', kt: 'kotlin',
                c: 'c', h: 'c', cpp: 'cpp', cs: 'csharp', sh: 'shell', bash: 'shell',
                xml: 'xml', html: 'html', htm: 'html', css: 'css', scss: 'scss', md: 'markdown',
                sql: 'sql', lua: 'lua',
            };
            return map[ext] || 'plaintext';
        }

        function serverConsole(c) {
            // Kept outside the reactive object: Alpine's proxy would rebind the
            // WebSocket's methods and break send().
            let socket = null, reconnectTimer = null, closed = false, started = false;

            return {
                tab: c.tab, stats: { state: 'loading' }, logs: '', commandInput: '',
                labels: { running: 'Running', exited: 'Offline', created: 'Installed (stopped)', restarting: 'Restarting', missing: 'Not installed', loading: 'Loading…', unreachable: 'Node unreachable' },
                get stateLabel() { return this.labels[this.stats.state] || (this.stats.state || 'Unknown'); },
                get stateColor() {
                    if (this.stats.state === 'running') return 'bg-green-500';
                    if (this.stats.state === 'unreachable') return 'bg-red-500';
                    if (this.stats.state === 'restarting' || this.stats.state === 'loading') return 'bg-amber-400';
                    return 'bg-gray-400';
                },
                // A server can only be started when it's installed but stopped,
                // and only stopped/restarted while it's running.
                get canStart() { return ['exited', 'created'].includes(this.stats.state); },
                get canStop() { return this.stats.state === 'running'; },
                get canRestart() { return this.stats.state === 'running'; },
                strip(s) { return (s || '').replace(/\x1b\[[0-9;]*m/g, ''); },

                init() {
                    // Alpine auto-calls init() AND we also declare x-init="init()";
                    // guard so the console connects exactly once (a double connect
                    // would stream every log line twice).
                    if (started) return;
                    started = true;

                    // Keep the URL in sync with the active tab so a reload lands
                    // on the same one (e.g. /servers/5/startup).
                    if (c.basePath) {
                        this.$watch('tab', (t) => window.history.replaceState({}, '', c.basePath + '/' + t));
                    }

                    this.connect();
                    window.addEventListener('beforeunload', () => { closed = true; if (socket) socket.close(); });
                },

                // Open (or reopen) the console WebSocket to the node daemon.
                async connect() {
                    // Drop any previous socket so reconnects don't stack followers.
                    if (socket) { try { socket.onclose = null; socket.onmessage = null; socket.close(); } catch (e) {} socket = null; }

                    let info;
                    try {
                        info = await (await fetch(c.wsInfoUrl)).json();
                    } catch (e) {
                        this.stats = { state: 'unreachable' };
                        return this.scheduleReconnect();
                    }

                    let ws;
                    try {
                        ws = new WebSocket(info.socket);
                    } catch (e) {
                        this.stats = { state: 'unreachable' };
                        return this.scheduleReconnect();
                    }
                    socket = ws;

                    ws.onopen = () => ws.send(JSON.stringify({ event: 'auth', args: [info.token] }));
                    ws.onmessage = (ev) => this.onMessage(ev.data);
                    ws.onclose = () => { if (!closed) { this.stats = { ...this.stats, state: 'unreachable' }; this.scheduleReconnect(); } };
                    ws.onerror = () => ws.close();
                },

                scheduleReconnect() {
                    if (reconnectTimer || closed) return;
                    reconnectTimer = setTimeout(() => { reconnectTimer = null; this.connect(); }, 3000);
                },

                onMessage(data) {
                    let m;
                    try { m = JSON.parse(data); } catch (e) { return; }
                    const a = m.args || [];
                    switch (m.event) {
                        case 'console output':
                            this.append(this.strip(a[0] || ''));
                            break;
                        case 'status':
                            this.stats = { ...this.stats, state: a[0] };
                            break;
                        case 'stats':
                            try { this.stats = JSON.parse(a[0]); } catch (e) {}
                            break;
                        case 'error':
                            this.append('\n[error] ' + (a[0] || '') + '\n');
                            break;
                    }
                },

                append(text) {
                    this.logs += text;
                    if (this.logs.length > 200000) this.logs = this.logs.slice(-200000);
                    this.$nextTick(() => {
                        if (this.$refs.console) this.$refs.console.scrollTop = this.$refs.console.scrollHeight;
                    });
                },

                async power(action) {
                    // Ignore clicks on a button that isn't valid for the current state.
                    if ((action === 'start' && !this.canStart) ||
                        (action === 'stop' && !this.canStop) ||
                        (action === 'restart' && !this.canRestart)) return;

                    // Confirm the disruptive actions before sending them.
                    if (action === 'stop' || action === 'restart') {
                        const r = await window.yunoConfirm({
                            title: action === 'stop' ? 'Stop the server?' : 'Restart the server?',
                            text: action === 'stop' ? 'Players will be disconnected.' : 'The server will briefly go offline.',
                            confirmButtonText: action === 'stop' ? 'Stop' : 'Restart',
                        });
                        if (!r.isConfirmed) return;
                    }

                    try {
                        const res = await fetch(c.powerUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': c.csrf }, body: JSON.stringify({ action }) });
                        if (res.ok) window.yunoToast('Power action sent: ' + action);
                        else window.yunoToast('Could not reach the node daemon.', 'error');
                    } catch (e) {
                        window.yunoToast('Could not reach the node daemon.', 'error');
                    }
                },

                sendCommand() {
                    const cmd = this.commandInput.trim();
                    if (!cmd || !socket || socket.readyState !== WebSocket.OPEN) return;
                    this.commandInput = '';
                    this.append('> ' + cmd + '\n');
                    socket.send(JSON.stringify({ event: 'command', args: [cmd] }));
                },
            };
        }

        function fileManager(c) {
            // Monaco editor instance kept out of Alpine's reactive proxy.
            let editor = null;

            return {
                path: '/', entries: [], editing: null, contents: '', search: '', monacoReady: false, selected: [],
                // Entries matching the search box (folders already sorted first).
                get filtered() {
                    const q = this.search.trim().toLowerCase();
                    return q ? this.entries.filter((e) => e.name.toLowerCase().includes(q)) : this.entries;
                },
                get allSelected() {
                    return this.filtered.length > 0 && this.filtered.every((e) => this.selected.includes(e.name));
                },
                toggleAll() {
                    const names = this.filtered.map((e) => e.name);
                    this.selected = this.allSelected
                        ? this.selected.filter((n) => !names.includes(n))
                        : [...new Set([...this.selected, ...names])];
                },
                fmtSize(bytes) {
                    if (bytes == null) return '';
                    const u = ['B', 'KB', 'MB', 'GB']; let i = 0, n = bytes;
                    while (n >= 1024 && i < u.length - 1) { n /= 1024; i++; }
                    return (i === 0 ? n : n.toFixed(1)) + ' ' + u[i];
                },
                fmtDate(ts) { return ts ? new Date(ts * 1000).toLocaleString() : ''; },
                fullPath(name) { return (this.path === '/' ? '' : this.path) + '/' + name; },
                // Pick an icon based on the file's extension.
                fileIcon(e) {
                    if (e.directory) return '📁';
                    const name = e.name.toLowerCase();
                    const ext = name.includes('.') ? name.split('.').pop() : '';
                    const groups = {
                        '🖼️': ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'bmp'],
                        '⚙️': ['json', 'yml', 'yaml', 'toml', 'ini', 'conf', 'cfg', 'properties', 'env', 'lock'],
                        '📜': ['js', 'mjs', 'cjs', 'ts', 'py', 'php', 'go', 'rs', 'java', 'kt', 'c', 'h', 'cpp', 'cs', 'lua', 'rb', 'sh', 'bash'],
                        '📦': ['zip', 'tar', 'gz', 'tgz', 'rar', '7z', 'jar'],
                        '🌐': ['html', 'htm', 'xml'],
                        '🎨': ['css', 'scss', 'sass'],
                        '🗄️': ['db', 'sqlite', 'sql', 'mca', 'dat'],
                        '📋': ['log'],
                        '📝': ['md', 'txt'],
                    };
                    for (const icon in groups) {
                        if (groups[icon].includes(ext)) return icon;
                    }
                    return '📄';
                },
                async load() {
                    this.search = '';
                    this.selected = [];
                    try {
                        const r = await (await fetch(c.base + '?path=' + encodeURIComponent(this.path))).json();
                        // Folders first, then files, each sorted case-insensitively by name.
                        this.entries = (r.entries || []).sort((a, b) =>
                            a.directory !== b.directory
                                ? (a.directory ? -1 : 1)
                                : a.name.localeCompare(b.name, undefined, { sensitivity: 'base' }));
                        this.closeEditor();
                    } catch (e) { this.entries = []; }
                },
                open(e) {
                    if (e.directory) { this.path = (this.path === '/' ? '' : this.path) + '/' + e.name; this.load(); }
                    else { this.read(e.name); }
                },
                async deleteEntry(e) {
                    const r = await window.yunoConfirm({
                        title: 'Delete "' + e.name + '"?',
                        text: e.directory ? 'The folder and everything in it will be removed.' : 'This file will be removed.',
                        icon: 'warning', confirmButtonText: 'Delete',
                    });
                    if (r.isConfirmed) this.deletePaths([e.name]);
                },
                async deleteSelected() {
                    if (!this.selected.length) return;
                    const r = await window.yunoConfirm({
                        title: 'Delete ' + this.selected.length + ' item(s)?',
                        text: 'The selected files and folders will be removed.',
                        icon: 'warning', confirmButtonText: 'Delete',
                    });
                    if (r.isConfirmed) this.deletePaths([...this.selected]);
                },
                async deletePaths(names) {
                    const paths = names.map((n) => this.fullPath(n));
                    try {
                        const res = await fetch(c.deleteUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': c.csrf }, body: JSON.stringify({ paths }) });
                        if (res.ok) window.yunoToast(names.length + ' {{ __('deleted') }}');
                        else window.yunoToast('{{ __('Could not delete.') }}', 'error');
                    } catch (e) {
                        window.yunoToast('{{ __('Could not delete.') }}', 'error');
                    }
                    this.load();
                },
                async read(name) {
                    const p = (this.path === '/' ? '' : this.path) + '/' + name;
                    const r = await (await fetch(c.readUrl + '?path=' + encodeURIComponent(p))).json();
                    this.editing = p; this.contents = r.contents ?? '';
                    this.mountEditor();
                },
                // Mount Monaco into the modal; fall back to the plain textarea if
                // it can't be loaded (e.g. offline).
                async mountEditor() {
                    let monaco;
                    try {
                        monaco = await loadMonaco();
                    } catch (e) {
                        this.monacoReady = false; // textarea fallback stays visible
                        return;
                    }
                    // Reveal the container first so Monaco lays out at full size.
                    this.monacoReady = true;
                    await this.$nextTick();
                    if (editor) { editor.dispose(); editor = null; }
                    editor = monaco.editor.create(this.$refs.editor, {
                        value: this.contents,
                        language: monacoLanguage(this.editing),
                        theme: document.documentElement.classList.contains('dark') ? 'vs-dark' : 'vs',
                        automaticLayout: true,
                        fontSize: 13,
                        minimap: { enabled: true },
                        scrollBeyondLastLine: false,
                        tabSize: 2,
                    });
                },
                closeEditor() {
                    if (editor) { editor.dispose(); editor = null; }
                    this.monacoReady = false;
                    this.editing = null;
                },
                up() { this.path = this.path.replace(/\/[^/]*$/, '') || '/'; this.load(); },
                async save() {
                    const contents = (editor && this.monacoReady) ? editor.getValue() : this.contents;
                    try {
                        const res = await fetch(c.writeUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': c.csrf }, body: JSON.stringify({ path: this.editing, contents }) });
                        if (res.ok) window.yunoToast('{{ __('File saved') }}');
                        else window.yunoToast('{{ __('Could not save file.') }}', 'error');
                    } catch (e) {
                        window.yunoToast('{{ __('Could not save file.') }}', 'error');
                    }
                },
            };
        }

        function startupEditor(c) {
            // Seed each editable variable's value from the server, falling back
            // to the egg default.
            const values = {};
            (c.variables || []).forEach((v) => {
                const saved = c.current.values ? c.current.values[v.env_variable] : undefined;
                values[v.env_variable] = (saved !== undefined && saved !== null) ? saved : (v.default_value ?? '');
            });

            return {
                variables: c.variables || [],
                server: c.current,
                startup: c.current.startup || '',
                dockerImage: c.current.dockerImage || '',
                values,
                showPreview: true,

                // Egg presets, plus the current command/image if it isn't one of
                // them, so nothing selected gets silently dropped.
                get presetOptions() {
                    const list = (c.presets || []).slice();
                    if (this.startup && !list.some((p) => p.command === this.startup)) {
                        list.unshift({ name: 'Current', command: this.startup });
                    }
                    return list;
                },
                get imageOptions() {
                    const list = (c.images || []).slice();
                    if (this.dockerImage && !list.some((i) => i.ref === this.dockerImage)) {
                        list.unshift({ label: 'Current', ref: this.dockerImage });
                    }
                    return list;
                },

                // The startup command with its variable placeholders resolved.
                get preview() {
                    let cmd = this.startup || '';
                    const env = Object.assign({}, this.values, {
                        SERVER_MEMORY: this.server.memory,
                        SERVER_PORT: this.server.port,
                        SERVER_IP: this.server.ip,
                    });
                    // Build the "{{" / "}}" markers by concatenation so Blade
                    // doesn't try to parse them as echoes.
                    const open = '{' + '{', close = '}' + '}';
                    for (const k of Object.keys(env)) {
                        cmd = cmd.split(open + k + close).join(env[k] ?? '');
                    }
                    return cmd;
                },
            };
        }
    </script>
</x-app-layout>
