<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    @php
        $tabs = [
            'account' => [
                'label' => __('Account'),
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            ],
            'appearance' => [
                'label' => __('Appearance'),
                'icon' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
            ],
            'security' => [
                'label' => __('Security'),
                'icon' => 'M12 3l7 4v5c0 4.418-3.134 7.582-7 9-3.866-1.418-7-4.582-7-9V7l7-4z',
            ],
            'api' => [
                'label' => __('API Keys'),
                'icon' => 'M15 7a4 4 0 11-3.874 5H9v2H7v2H3v-3l5.126-5.126A4 4 0 0115 7z',
            ],
            'danger' => [
                'label' => __('Danger Zone'),
                'icon' => 'M12 9v2m0 4h.01M10.29 3.86l-8.48 14.7A1 1 0 003.18 20h17.64a1 1 0 00.87-1.5l-8.48-14.7a1 1 0 00-1.74 0z',
            ],
        ];
    @endphp

    <div class="py-10" x-data="{
            tab: localStorage.getItem('profileTab') || 'account',
            select(t) { this.tab = t; localStorage.setItem('profileTab', t); }
         }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tab navigation: modern segmented pills -->
            <div class="mb-8 overflow-x-auto">
                <nav class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 p-1.5 ring-1 ring-gray-200 dark:ring-gray-700 shadow-sm">
                    @foreach ($tabs as $key => $tab)
                        <button type="button" @click="select('{{ $key }}')"
                                :class="tab === '{{ $key }}'
                                    ? '{{ $key === 'danger' ? 'bg-white dark:bg-gray-700 text-red-600 dark:text-red-400 shadow-sm ring-1 ring-black/5' : 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm ring-1 ring-black/5' }}'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                                class="group inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold tracking-tight transition-all duration-150 focus:outline-none">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}" />
                            </svg>
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>

            <!-- Account -->
            <div x-show="tab === 'account'" class="space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <!-- Appearance -->
            <div x-show="tab === 'appearance'" x-cloak>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl">
                    <div class="max-w-xl">
                        @include('profile.partials.appearance-form')
                    </div>
                </div>
            </div>

            <!-- Security (2FA) -->
            <div x-show="tab === 'security'" x-cloak>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl">
                    <div class="max-w-xl">
                        @include('profile.partials.two-factor-form')
                    </div>
                </div>
            </div>

            <!-- API Keys -->
            <div x-show="tab === 'api'" x-cloak>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/60 sm:rounded-xl">
                    <div class="max-w-xl">
                        @include('profile.partials.api-keys')
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div x-show="tab === 'danger'" x-cloak>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-red-100 dark:ring-red-900/40 sm:rounded-xl">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
