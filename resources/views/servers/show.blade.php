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
                 x-data="fileManager({ base: '{{ route('servers.files', $server) }}', readUrl: '{{ route('servers.files.read', $server) }}', writeUrl: '{{ route('servers.files.write', $server) }}', csrf: '{{ csrf_token() }}' })"
                 x-init="load()">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-3">
                        <button @click="up()" x-show="path !== '/'" class="hover:underline">⬑ {{ __('up') }}</button>
                        <span class="font-mono" x-text="path"></span>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="e in entries" :key="e.name">
                            <li class="py-2 flex items-center justify-between text-sm">
                                <button @click="open(e)" class="flex items-center gap-2 text-gray-800 dark:text-gray-200 hover:text-indigo-600">
                                    <span x-text="e.directory ? '📁' : '📄'"></span>
                                    <span x-text="e.name"></span>
                                </button>
                                <span class="text-xs text-gray-400" x-show="!e.directory" x-text="e.size + ' B'"></span>
                            </li>
                        </template>
                        <li x-show="entries.length === 0" class="py-3 text-sm text-gray-500 dark:text-gray-400">{{ __('Empty or unreachable.') }}</li>
                    </ul>

                    <div x-show="editing" x-cloak class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-sm text-gray-700 dark:text-gray-300" x-text="editing"></span>
                            <button @click="save()" class="px-3 py-1.5 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">{{ __('Save') }}</button>
                        </div>
                        <textarea x-model="contents" rows="14" spellcheck="false"
                                  class="block w-full font-mono text-xs rounded-md border-gray-300 dark:border-gray-600 bg-gray-900 text-gray-100"></textarea>
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
            return {
                path: '/', entries: [], editing: null, contents: '',
                async load() {
                    try {
                        const r = await (await fetch(c.base + '?path=' + encodeURIComponent(this.path))).json();
                        this.entries = r.entries || []; this.editing = null;
                    } catch (e) { this.entries = []; }
                },
                open(e) {
                    if (e.directory) { this.path = (this.path === '/' ? '' : this.path) + '/' + e.name; this.load(); }
                    else { this.read(e.name); }
                },
                async read(name) {
                    const p = (this.path === '/' ? '' : this.path) + '/' + name;
                    const r = await (await fetch(c.readUrl + '?path=' + encodeURIComponent(p))).json();
                    this.editing = p; this.contents = r.contents ?? '';
                },
                up() { this.path = this.path.replace(/\/[^/]*$/, '') || '/'; this.load(); },
                async save() {
                    await fetch(c.writeUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': c.csrf }, body: JSON.stringify({ path: this.editing, contents: this.contents }) });
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
