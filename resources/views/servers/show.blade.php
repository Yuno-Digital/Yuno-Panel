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
    @endphp

    <div class="py-10" x-data="serverConsole({
            statsUrl: '{{ route('servers.stats', $server) }}',
            logsUrl: '{{ route('servers.logs', $server) }}',
            powerUrl: '{{ route('servers.power', $server) }}',
            csrf: '{{ csrf_token() }}',
            tab: 'console'
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
                    {{ __('Not installed yet — click Install to create the server.') }}
                </span>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    CPU <span class="font-medium text-gray-800 dark:text-gray-200" x-text="(stats.cpu_percent ?? 0).toFixed(1) + '%'"></span>
                    · RAM <span class="font-medium text-gray-800 dark:text-gray-200" x-text="Math.round(stats.memory_mb ?? 0) + ' / ' + Math.round(stats.memory_limit_mb ?? {{ $server->memory_mb }}) + ' MB'"></span>
                </div>
                <div class="ms-auto flex items-center gap-2">
                    <button @click="power('start')" class="px-3 py-1.5 rounded-md text-sm font-medium bg-green-600 text-white hover:bg-green-700">{{ __('Start') }}</button>
                    <button @click="power('restart')" class="px-3 py-1.5 rounded-md text-sm font-medium bg-amber-500 text-white hover:bg-amber-600">{{ __('Restart') }}</button>
                    <button @click="power('stop')" class="px-3 py-1.5 rounded-md text-sm font-medium bg-red-600 text-white hover:bg-red-700">{{ __('Stop') }}</button>
                    <form method="POST" action="{{ route('servers.install', $server) }}" onsubmit="return confirm('Reinstall the container? Files are kept.')">
                        @csrf
                        <x-secondary-button>{{ __('Install') }}</x-secondary-button>
                    </form>
                </div>
            </div>

            {{-- Tabs --}}
            <nav class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 p-1.5 ring-1 ring-gray-200 dark:ring-gray-700">
                @foreach (['console' => __('Console'), 'files' => __('Files'), 'settings' => __('Settings')] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                            class="rounded-lg px-4 py-2 text-sm font-semibold">{{ $label }}</button>
                @endforeach
            </nav>

            {{-- Console --}}
            <div x-show="tab === 'console'">
                <pre x-ref="console" class="h-96 overflow-auto rounded-lg bg-gray-900 text-gray-100 text-xs font-mono p-4 whitespace-pre-wrap" x-text="logs || '{{ __('Waiting for output…') }}'"></pre>
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

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Startup variables') }}</h3>
                    @if ($editable->isEmpty())
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('There are no editable settings for this server.') }}</p>
                    @else
                        <form method="POST" action="{{ route('servers.update', $server) }}" class="mt-5 space-y-5">
                            @csrf @method('PATCH')
                            @foreach ($editable as $variable)
                                <div>
                                    <x-input-label :for="'var_'.$variable->id" :value="$variable->name" />
                                    <x-text-input :id="'var_'.$variable->id" type="text" class="mt-1 block w-full font-mono text-sm"
                                                  :name="'variables['.$variable->env_variable.']'"
                                                  :value="old('variables.'.$variable->env_variable, $values[$variable->env_variable] ?? $variable->default_value)" />
                                    <x-input-error :messages="$errors->get('variables.'.$variable->env_variable)" class="mt-2" />
                                </div>
                            @endforeach
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>

            <a href="{{ route('servers.index') }}" class="inline-block text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; {{ __('Back to servers') }}</a>
        </div>
    </div>

    <script>
        function serverConsole(c) {
            return {
                tab: c.tab, stats: { state: 'loading' }, logs: '',
                labels: { running: 'Running', exited: 'Offline', created: 'Installed (stopped)', restarting: 'Restarting', missing: 'Not installed', loading: 'Loading…', unreachable: 'Node unreachable' },
                get stateLabel() { return this.labels[this.stats.state] || (this.stats.state || 'Unknown'); },
                get stateColor() {
                    if (this.stats.state === 'running') return 'bg-green-500';
                    if (this.stats.state === 'unreachable') return 'bg-red-500';
                    if (this.stats.state === 'restarting' || this.stats.state === 'loading') return 'bg-amber-400';
                    return 'bg-gray-400';
                },
                init() { this.poll(); setInterval(() => this.poll(), 3000); },
                async poll() {
                    try {
                        this.stats = await (await fetch(c.statsUrl)).json();
                        const l = await (await fetch(c.logsUrl)).json();
                        const atBottom = true;
                        this.logs = l.logs || '';
                        this.$nextTick(() => { if (this.$refs.console) this.$refs.console.scrollTop = this.$refs.console.scrollHeight; });
                    } catch (e) { this.stats = { state: 'unreachable' }; }
                },
                async power(action) {
                    await fetch(c.powerUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': c.csrf }, body: JSON.stringify({ action }) });
                    setTimeout(() => this.poll(), 800);
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
    </script>
</x-app-layout>
