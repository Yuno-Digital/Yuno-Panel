<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-100" />
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
                                   'group inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold tracking-tight transition-all duration-150',
                                   'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm ring-1 ring-black/5' => $isActive,
                                   'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' => ! $isActive,
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
