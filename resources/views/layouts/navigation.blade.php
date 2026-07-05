<nav x-data="{ open: false }" class="sticky top-0 z-40 glass border-b border-white/50 dark:border-white/5">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-fuchsia-500 shadow-glow transition-transform duration-300 group-hover:scale-105">
                            <x-application-logo class="h-5 w-5 fill-current text-white" />
                        </span>
                        <span class="hidden sm:block text-lg font-extrabold tracking-tight text-gradient">{{ config('app.name', 'Yuno') }}</span>
                    </a>
                </div>

                <!-- Navigation Links: modern segmented pills -->
                @php
                    $navLinks = [
                        [
                            'route' => 'dashboard',
                            'pattern' => 'dashboard',
                            'label' => __('Dashboard'),
                            'icon' => 'M3 12l9-7 9 7M5 10v9a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1v-9',
                        ],
                        [
                            'route' => 'servers.index',
                            'pattern' => 'servers.*',
                            'label' => __('Servers'),
                            'icon' => 'M5 5h14a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1zM5 14h14a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3a1 1 0 011-1zM8 7.5h.01M8 16.5h.01',
                        ],
                    ];
                    if (Auth::user()->is_admin) {
                        $navLinks[] = [
                            'route' => 'admin.dashboard',
                            'pattern' => 'admin.*',
                            'label' => __('Admin'),
                            'icon' => 'M9 12l2 2 4-4M12 3l7 4v5c0 4.418-3.134 7.582-7 9-3.866-1.418-7-4.582-7-9V7l7-4z',
                        ];
                    }
                @endphp
                <div class="hidden sm:flex sm:items-center sm:ms-8">
                    <div class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-900/60 p-1.5 ring-1 ring-gray-200 dark:ring-gray-700">
                        @foreach ($navLinks as $link)
                            @php $isActive = request()->routeIs($link['pattern']); @endphp
                            <a href="{{ route($link['route']) }}"
                               @class([
                                   'group inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold tracking-tight transition-all duration-200',
                                   'bg-gradient-to-r from-brand-500 to-fuchsia-500 text-white shadow-glow' => $isActive,
                                   'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-white/60 dark:hover:bg-white/5' => ! $isActive,
                               ])>
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                                </svg>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="hidden sm:flex sm:items-center sm:ms-6"
                 x-data="notifications('{{ route('notifications.index') }}', '{{ route('notifications.read') }}', '{{ csrf_token() }}')" x-init="start()">
                <div class="relative" @click.outside="open = false">
                    <button @click="toggle()" class="relative p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread"
                              class="absolute -top-0.5 -right-0.5 min-w-[1rem] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center"></span>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute right-0 mt-2 w-80 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 z-50">
                        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Notifications') }}</span>
                            <button @click="markAllRead()" x-show="unread > 0" class="text-xs text-indigo-600 hover:underline">{{ __('Mark all read') }}</button>
                        </div>
                        <div class="max-h-96 overflow-auto divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="n in items" :key="n.id">
                                <a :href="n.url || '#'" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40" :class="!n.read ? 'bg-indigo-50/60 dark:bg-indigo-900/10' : ''">
                                    <div class="flex items-start gap-2">
                                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0" :class="!n.read ? 'bg-indigo-500' : 'bg-transparent'"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100" x-text="n.title"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="n.message"></p>
                                            <p class="text-[11px] text-gray-400 mt-0.5" x-text="n.at"></p>
                                        </div>
                                    </div>
                                </a>
                            </template>
                            <div x-show="items.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No notifications') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 dark:text-gray-400 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 dark:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('servers.index')" :active="request()->routeIs('servers.*')">
                {{ __('Servers') }}
            </x-responsive-nav-link>
            @if (Auth::user()->is_admin)
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                    {{ __('Admin') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-100">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
