@csrf
@php
    $inp = 'mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $mono = $inp.' font-mono text-sm';

    // Eggs payload for the reactive form.
    $eggsData = $eggs->map(fn ($e) => [
        'id' => $e->id,
        'name' => $e->name,
        'docker_images' => (object) ($e->docker_images ?? []),
        'startup_commands' => collect($e->startup_commands ?? [])
            ->map(fn ($c) => is_array($c)
                ? ['name' => $c['name'] ?? '', 'command' => $c['command'] ?? '']
                : ['name' => '', 'command' => (string) $c])->values(),
        'variables' => $e->variables->map(fn ($v) => [
            'name' => $v->name,
            'env_variable' => $v->env_variable,
            'description' => $v->description,
            'default_value' => $v->default_value,
            'user_editable' => (bool) $v->user_editable,
        ])->values(),
    ])->values();

    // Current server's variable values, keyed by env name (edit).
    $serverValues = $server->relationLoaded('variables')
        ? $server->variables->mapWithKeys(fn ($sv) => [optional($sv->eggVariable)->env_variable => $sv->variable_value])
            ->filter(fn ($v, $k) => $k !== null && $k !== '')->all()
        : [];
@endphp

<div x-data="serverForm({
        eggs: {{ Illuminate\Support\Js::from($eggsData) }},
        eggId: '{{ old('egg_id', $server->egg_id) }}',
        dockerImage: @js(old('docker_image', $server->docker_image)),
        startup: @js(old('startup', $server->startup)),
        vars: {{ Illuminate\Support\Js::from(old('variables', $serverValues)) }},
        nodeId: '{{ old('node_id', $server->node_id) }}',
        allocations: {{ Illuminate\Support\Js::from($allocations) }},
        allocationId: '{{ old('allocation_id', $server->allocation_id) }}'
     })" x-init="init()" class="space-y-6">

    {{-- Basics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="name" :value="__('Server name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $server->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="owner_id" :value="__('Owner')" />
            <select id="owner_id" name="owner_id" class="{{ $inp }}" required>
                <option value="">{{ __('— Select owner —') }}</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $server->owner_id) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('owner_id')" class="mt-2" />
        </div>
    </div>

    {{-- Icon: a URL or an uploaded image; empty falls back to the egg's icon --}}
    <div x-data="{ icon: {{ Illuminate\Support\Js::from(old('icon', $server->icon)) }} }">
        <x-input-label :value="__('Icon')" />
        <p class="mt-1 mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Optional. A URL or an uploaded image (data:image, max ~256 KB). Leave empty to use the egg\'s icon.') }}</p>
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 shrink-0 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden">
                <template x-if="icon"><img :src="icon" alt="" class="w-full h-full object-contain"></template>
                <template x-if="!icon"><span class="text-xs text-gray-400">—</span></template>
            </div>
            <div class="flex-1 space-y-2">
                <input type="text" name="icon" x-model="icon" placeholder="https://…  {{ __('or') }}  data:image/…" class="{{ $mono }} block w-full">
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
            <x-input-label for="node_id" :value="__('Node')" />
            <select id="node_id" name="node_id" class="{{ $inp }}" x-model="nodeId" required>
                <option value="">{{ __('— Select node —') }}</option>
                @foreach ($nodes as $n)
                    <option value="{{ $n->id }}">{{ $n->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('node_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" class="{{ $inp }}" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $server->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Allocation (depends on node; must be created on the node first) --}}
    <div>
        <x-input-label for="allocation_id" :value="__('Allocation (IP:port)')" />
        <select id="allocation_id" name="allocation_id" class="{{ $inp }}" x-model="allocationId" required>
            <option value="">{{ __('— Select allocation —') }}</option>
            <template x-for="a in nodeAllocations" :key="a.id">
                <option :value="a.id" x-text="a.ip + ':' + a.port"></option>
            </template>
        </select>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="nodeId && nodeAllocations.length === 0">
            {{ __('This node has no free allocations. Create some on the node first.') }}
        </p>
        <x-input-error :messages="$errors->get('allocation_id')" class="mt-2" />
    </div>

    {{-- Egg selection --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <x-input-label for="egg_id" :value="__('Egg')" />
        <select id="egg_id" name="egg_id" class="{{ $inp }}" x-model="eggId" @change="onEggChange()" required>
            <option value="">{{ __('— Select egg —') }}</option>
            @foreach ($eggs as $e)
                <option value="{{ $e->id }}">{{ $e->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('egg_id')" class="mt-2" />
    </div>

    {{-- Egg-dependent fields --}}
    <div x-show="egg" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="docker_image" :value="__('Docker image')" />
                <select id="docker_image" name="docker_image" class="{{ $inp }}" x-model="dockerImage">
                    <template x-for="entry in imageEntries" :key="entry[1]">
                        <option :value="entry[1]" x-text="entry[0]"></option>
                    </template>
                </select>
                <x-input-error :messages="$errors->get('docker_image')" class="mt-2" />
            </div>
            <div>
                <x-input-label :value="__('Startup preset')" />
                <select class="{{ $inp }}" @change="startup = $event.target.value">
                    <template x-for="(s, i) in startups" :key="i">
                        <option :value="s.command" x-text="s.name || s.command"></option>
                    </template>
                </select>
            </div>
        </div>

        <div>
            <x-input-label for="startup" :value="__('Startup command')" />
            <textarea id="startup" name="startup" rows="3" class="{{ $mono }}" x-model="startup"></textarea>
            <x-input-error :messages="$errors->get('startup')" class="mt-2" />
        </div>

        {{-- Egg variables --}}
        <div x-show="variables.length" class="space-y-4">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Variables') }}</h4>
            <template x-for="v in variables" :key="v.env_variable">
                <div>
                    <div class="flex items-center justify-between">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" x-text="v.name"></label>
                        <code class="text-xs text-gray-400" x-text="'{' + '{' + v.env_variable + '}' + '}'"></code>
                    </div>
                    <input type="text" :name="`variables[${v.env_variable}]`" x-model="vars[v.env_variable]"
                           class="{{ $mono }}" :readonly="!v.user_editable" :class="{ 'opacity-60': !v.user_editable }">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="v.description"></p>
                </div>
            </template>
        </div>
    </div>

    {{-- Resource limits --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <x-input-label for="memory_mb" :value="__('Memory (MB)')" />
            <x-text-input id="memory_mb" name="memory_mb" type="number" class="mt-1 block w-full" :value="old('memory_mb', $server->memory_mb)" required />
            <x-input-error :messages="$errors->get('memory_mb')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="swap_mb" :value="__('Swap (MB)')" />
            <x-text-input id="swap_mb" name="swap_mb" type="number" class="mt-1 block w-full" :value="old('swap_mb', $server->swap_mb ?? 0)" required />
        </div>
        <div>
            <x-input-label for="disk_mb" :value="__('Disk (MB)')" />
            <x-text-input id="disk_mb" name="disk_mb" type="number" class="mt-1 block w-full" :value="old('disk_mb', $server->disk_mb)" required />
            <x-input-error :messages="$errors->get('disk_mb')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="cpu" :value="__('CPU (%)')" />
            <x-text-input id="cpu" name="cpu" type="number" class="mt-1 block w-full" :value="old('cpu', $server->cpu ?? 0)" required />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('0 = unlimited') }}</p>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save server') }}</x-primary-button>
        <a href="{{ route('admin.servers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>

<script>
    function serverForm(initial) {
        return {
            eggs: initial.eggs,
            eggId: initial.eggId,
            dockerImage: initial.dockerImage || '',
            startup: initial.startup || '',
            vars: initial.vars || {},
            nodeId: initial.nodeId || '',
            allocations: initial.allocations || [],
            allocationId: initial.allocationId || '',

            init() {
                // Populate defaults if an egg is already selected (edit / old input).
                if (this.eggId) this.onEggChange(true);
            },

            // Free allocations on the selected node (plus the one already bound
            // to this server, so it stays selectable on edit).
            get nodeAllocations() {
                return this.allocations.filter(a =>
                    String(a.node_id) === String(this.nodeId)
                    && (a.server_id === null || String(a.id) === String(this.allocationId)));
            },

            get egg() {
                return this.eggs.find(e => String(e.id) === String(this.eggId)) || null;
            },
            get imageEntries() {
                return this.egg ? Object.entries(this.egg.docker_images || {}) : [];
            },
            get startups() {
                return this.egg ? (this.egg.startup_commands || []) : [];
            },
            get variables() {
                return this.egg ? (this.egg.variables || []) : [];
            },

            onEggChange(keepExisting = false) {
                if (!this.egg) return;
                const images = Object.values(this.egg.docker_images || {});
                if (!keepExisting || !images.includes(this.dockerImage)) {
                    this.dockerImage = images[0] || '';
                }
                if (!keepExisting || !this.startup) {
                    this.startup = (this.startups[0] && this.startups[0].command) || '';
                }
                (this.egg.variables || []).forEach(v => {
                    if (this.vars[v.env_variable] === undefined || this.vars[v.env_variable] === null) {
                        this.vars[v.env_variable] = v.default_value ?? '';
                    }
                });
            },
        };
    }
</script>
