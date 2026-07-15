<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route("dashboard") }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                @if (auth()->user()->role == 7)
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        @foreach ([
                                "dashboard" => "Dashboard",
                                "domains" => "Domains",
                                "pages" => "Pages",
                                "blog" => "Blog",
                                "menu" => "Menu",
                                "settings" => "Settings"
                            ]
                            as $key => $value)
                            <x-nav-link href="{{ route($key) }}" :active="request()->routeIs($key)">
                                {{ __($value) }}
                            </x-nav-link>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="hidden sm:gap-3 sm:flex sm:items-center sm:ms-6">
                <div x-data="{
                    darkmode: localStorage.getItem('darkmode') == 'enabled' ? true : false,
                }" class="text-xl cursor-pointer mt-2">
                    <i onclick="enableDarkMode()" x-on:click="darkmode = true" x-show="!darkmode" class="hgi hgi-stroke hgi-moon-02"></i>
                    <i onclick="disableDarkMode()" x-on:click="darkmode = false" x-show="darkmode" class="hgi hgi-stroke hgi-sun-03 text-yellow-500"></i>
                </div>
                <!-- Settings Dropdown -->
                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                        {{ Auth::user()->name }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            @if (auth()->user()->role == 7)
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __("Advance") }}
                                </div>
                                <x-dropdown-link href="{{ route('users') }}">
                                    {{ __("Manage Users") }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('themes') }}">
                                    {{ __("Themes") }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('updates') }}">
                                    {{ __("App Updates") }}
                                </x-dropdown-link>

                                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                            @endif

                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __("Manage Account") }}
                            </div>
                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __("Profile") }}
                            </x-dropdown-link>
                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __("API Tokens") }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route("logout") }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                    {{ __("Log Out") }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex gap-3 items-center sm:hidden">
                <div x-data="{
                    darkmode: localStorage.getItem('darkmode') == 'enabled' ? true : false,
                }" class="text-xl cursor-pointer mt-2">
                    <i onclick="enableDarkMode()" x-on:click="darkmode = true" x-show="!darkmode" class="hgi hgi-stroke hgi-moon-02"></i>
                    <i onclick="disableDarkMode()" x-on:click="darkmode = false" x-show="darkmode" class="hgi hgi-stroke hgi-sun-03 text-yellow-500"></i>
                </div>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        @if (auth()->user()->role == 7)
            <div class="pt-2 pb-3 space-y-1">
                @foreach ([
                        "dashboard" => "Dashboard",
                        "domains" => "Domains",
                        "pages" => "Pages",
                        "blog" => "Blog",
                        "menu" => "Menu",
                        "settings" => "Settings"
                    ]
                    as $key => $value)
                    <x-responsive-nav-link href="{{ route($key) }}" :active="request()->routeIs($key)">
                        {{ __($value) }}
                    </x-responsive-nav-link>
                @endforeach
            </div>
        @endif

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                @if (auth()->user()->role == 7)
                    <x-responsive-nav-link href="{{ route('users') }}" :active="request()->routeIs('users')">
                        {{ __("Manage Users") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('themes') }}" :active="request()->routeIs('themes')">
                        {{ __("Themes") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('updates') }}" :active="request()->routeIs('updates')">
                        {{ __("App Updates") }}
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __("Profile") }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __("API Tokens") }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route("logout") }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __("Log Out") }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
