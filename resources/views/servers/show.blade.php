<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ $server->name }}</h2>
            @php
                $color = match ($server->status) {
                    'running' => 'bg-green-100 text-green-800',
                    'starting', 'stopping' => 'bg-yellow-100 text-yellow-800',
                    default => 'bg-gray-200 text-gray-700',
                };
            @endphp
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }}">{{ ucfirst($server->status) }}</span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-800 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Overview --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    @php
                        $rows = [
                            __('Egg') => $server->egg?->name ?? '—',
                            __('Node') => $server->node?->name ?? '—',
                            __('Address') => $server->allocation?->address() ?? '—',
                            __('Memory') => \App\Support\Format::size($server->memory_mb),
                            __('Disk') => \App\Support\Format::size($server->disk_mb),
                            __('CPU') => $server->cpu ? $server->cpu.' %' : __('unlimited'),
                        ];
                    @endphp
                    @foreach ($rows as $label => $value)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900 dark:text-gray-100">{{ $value }}</dd>
                        </div>
                    @endforeach
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Docker image') }}</dt>
                        <dd class="mt-0.5 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $server->docker_image ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Startup command') }}</dt>
                        <dd class="mt-0.5 font-mono text-xs text-gray-700 dark:text-gray-300 break-all">{{ $server->startup ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Editable variables --}}
            @php
                $editable = $server->egg ? $server->egg->variables->where('user_editable', true) : collect();
                $values = $server->variables->mapWithKeys(fn ($sv) => [optional($sv->eggVariable)->env_variable => $sv->variable_value]);
            @endphp
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Startup variables') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Settings you can change for this server.') }}</p>

                @if ($editable->isEmpty())
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ __('There are no editable settings for this server.') }}</p>
                @else
                    <form method="POST" action="{{ route('servers.update', $server) }}" class="mt-5 space-y-5">
                        @csrf
                        @method('PATCH')
                        @foreach ($editable as $variable)
                            <div>
                                <x-input-label :for="'var_'.$variable->id" :value="$variable->name" />
                                <x-text-input :id="'var_'.$variable->id" type="text" class="mt-1 block w-full font-mono text-sm"
                                              :name="'variables['.$variable->env_variable.']'"
                                              :value="old('variables.'.$variable->env_variable, $values[$variable->env_variable] ?? $variable->default_value)" />
                                @if ($variable->description)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $variable->description }}</p>
                                @endif
                                <x-input-error :messages="$errors->get('variables.'.$variable->env_variable)" class="mt-2" />
                            </div>
                        @endforeach
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </form>
                @endif
            </div>

            <a href="{{ route('servers.index') }}" class="inline-block text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; {{ __('Back to servers') }}</a>
        </div>
    </div>
</x-app-layout>
