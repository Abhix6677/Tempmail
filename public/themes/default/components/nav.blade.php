<nav>
    <div class="bg-gray-100 dark:bg-gray-800 px-5 hidden lg:flex sticky top-0 z-40 h-16 items-center">
        <div class="w-full">
            <div class="flex items-center justify-between h-12">
                <div class="flex items-center">
                    <div class="flex items-baseline space-x-4">
                        @foreach ($menus as $menu)
                            @if ($menu->hasChild())
                                <div @click.away="open = false" class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex flex-row items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 font-semibold text-left bg-transparent rounded-lg md:w-auto md:inline hover:text-gray-900 dark:hover:text-white focus:text-gray-900 dark:focus:text-white hover:bg-gray-200 dark:hover:bg-gray-700 focus:bg-gray-200 dark:focus:bg-gray-700 focus:outline-none">
                                        <span>{!! __($menu->name) !!}</span>
                                        <svg fill="currentColor" viewBox="0 0 20 20" :class="{'rotate-180': open, 'rotate-0': !open}" class="inline w-4 h-4 mt-1 ml-1 transition-transform duration-200 transform md:-mt-1">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-0 w-full mt-2 origin-top-right rounded-md shadow-lg md:w-48">
                                        <div class="px-2 py-2 bg-white dark:bg-gray-800 rounded-lg shadow">
                                            @foreach ($menu->getChild() as $child)
                                                <a class="block px-4 py-2 mt-2 text-sm font-semibold bg-transparent rounded-lg text-gray-700 dark:text-gray-300 md:mt-0 hover:text-gray-900 dark:hover:text-white focus:text-gray-900 dark:focus:text-white hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none" href="{{ Util::localizeUrl($child->link) }}" target="{{ $child->target }}">{!! __($child->name) !!}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if ($menu->parent_id === null)
                                    <a href="{{ Util::localizeUrl($menu->link) }}" class="px-3 py-2 text-sm font-semibold text-left bg-transparent text-gray-700 dark:text-gray-300 {{ url()->current() === Util::localizeUrl($menu->link) ? "bg-gray-200 dark:bg-gray-700" : "" }} rounded-lg md:w-auto md:inline hover:text-gray-900 dark:hover:text-white focus:text-gray-900 dark:focus:text-white hover:bg-gray-200 dark:hover:bg-gray-700 focus:bg-gray-200 dark:focus:bg-gray-700 focus:outline-none" target="{{ $menu->target }}">{!! __($menu->name) !!}</a>
                                @endif
                            @endif
                        @endforeach

                        {{-- Admin button removed from frontend mailbox/navigation --}}

                        @if (config("app.settings.user_registration.enabled") && ! Auth::check())
                            <a href="{{ route("login") }}" class="px-3 py-2 text-sm font-semibold rounded-lg md:w-auto md:inline bg-secondary text-white">{{ __("Login") }}</a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if (config("app.settings.enable_dark_mode"))
                        <div x-data="{
                            darkmode: localStorage.getItem('darkmode') == 'enabled' ? true : false,
                        }" class="ml-3 text-lg cursor-pointer">
                            <i onclick="enableDarkMode()" x-on:click="darkmode = true" x-show="!darkmode" class="fas fa-moon"></i>
                            <i onclick="disableDarkMode()" x-on:click="darkmode = false" x-show="darkmode" class="fas fa-sun hgi-sun-03 text-yellow-500"></i>
                        </div>
                    @endif

                    @if (auth()->check())
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <button class="flex text-sm border border-gray-300 rounded-full focus:outline-none focus:border-gray-300 transition">
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
                                <!-- Account Management -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __("Manage Account") }}
                                </div>
                                <x-dropdown-link href="{{ route('profile') }}">
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
                    @endif

                    <div class="flex items-center">
                        <div class="relative">
                            <form action="{{ route("locale") }}" id="locale-form" method="post">
                                @csrf
                                <select class="border-gray-300 dark:border-gray-600 block appearance-none cursor-pointer py-1 rounded-md focus:outline-none bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300" name="locale" id="locale" x-on:change="$el.form.submit()">
                                    @foreach ((config("app.settings.languages") ?? []) as $code => $language)
                                        @if (is_array($language) && ($language["is_active"] ?? false))
                                            <option {{ app()->getLocale() == $code ? "selected" : "" }} value="{{ $code }}">{{ $language["label"] ?? $code }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div x-data="{ open: false }">
        <div class="flex items-center gap-3 lg:hidden absolute top-12 right-6">
            @if (config("app.settings.enable_dark_mode"))
                <div x-data="{
                    darkmode: localStorage.getItem('darkmode') == 'enabled' ? true : false,
                }" class="ml-3 text-lg cursor-pointer">
                    <i onclick="enableDarkMode()" x-on:click="darkmode = true" x-show="!darkmode" class="fas fa-moon text-white"></i>
                    <i onclick="disableDarkMode()" x-on:click="darkmode = false" x-show="darkmode" class="fas fa-sun hgi-sun-03 text-yellow-500"></i>
                </div>
            @endif

            @if (auth()->check())
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex text-sm border border-gray-300 rounded-full focus:outline-none focus:border-gray-300 transition">
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
                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __("Manage Account") }}
                        </div>
                        <x-dropdown-link href="{{ route('profile') }}">
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
            @endif

            <div @click="open = true" class="w-8 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </div>
        </div>
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0" x-transition:enter-end="transform opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100" x-transition:leave-end="transform opacity-0" @click.away="open = false" class="flex-col lg:hidden fixed top-0 left-0 min-h-screen w-full bg-black bg-opacity-75 backdrop-blur-sm">
            <div @click="open = false" class="absolute top-12 right-6 w-8 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="w-full mx-auto mt-20">
                <div class="flex flex-col items-center justify-between">
                    <div class="flex flex-col items-center space-y-2">
                        @foreach ($menus as $menu)
                            @if ($menu->hasChild())
                                <div @click.away="open = false" class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex flex-row items-center w-full px-3 py-2 text-sm text-white font-semibold text-left bg-transparent rounded-lg md:w-auto md:inline focus:text-gray-900 focus:bg-gray-200 focus:outline-none">
                                        <span>{!! __($menu->name) !!}</span>
                                        <svg fill="currentColor" viewBox="0 0 20 20" :class="{'rotate-180': open, 'rotate-0': !open}" class="inline w-4 h-4 ml-1 transition-transform duration-200 transform md:-mt-1">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-0 w-full mt-2 origin-top-right rounded-md shadow-lg md:w-48 z-10">
                                        <div class="px-2 py-2 text-center bg-white rounded-lg shadow dark-mode:bg-gray-800">
                                            @foreach ($menu->getChild() as $child)
                                                <a class="block text-sm font-semibold bg-transparent rounded-lg text-gray-600 md:mt-0 focus:text-gray-900 focus:bg-gray-100 focus:outline-none" href="{{ Util::localizeUrl($child->link) }}" target="{{ $child->target }}">{!! __($child->name) !!}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if ($menu->parent_id === null)
                                    <a href="{{ Util::localizeUrl($menu->link) }}" class="px-3 py-2 text-sm font-semibold text-left bg-transparent text-white {{ url()->current() === Util::localizeUrl($menu->link) ? "bg-gray-200" : "" }} rounded-lg md:w-auto md:inline focus:text-gray-900 focus:bg-gray-200 focus:outline-none" target="{{ $menu->target }}">{!! __($menu->name) !!}</a>
                                @endif
                            @endif
                        @endforeach

                        @if (Auth::check() && Auth::user()->role == 7)
                            <a href="{{ route("admin") }}" target="_blank" class="px-3 py-2 text-sm font-semibold text-left bg-transparent border-dashed border-2 border-red-400 text-red-400 rounded-lg md:w-auto md:inline hover:bg-red-100">{{ __("Admin") }}</a>
                        @endif
                    </div>
                    <div class="flex flex-col items-center space-y-2 mt-10">
                        <div class="text-white space-x-2">
                            @foreach (config("app.settings.socials", []) as $social)
                                <a href="{{ $social["link"] }}" target="_blank" class="text-lg" rel="noopener noreferrer"><i class="{{ $social["icon"] }}"></i></a>
                            @endforeach
                        </div>
                        <div class="flex items-center mt-4">
                            <div class="relative">
                                <form action="{{ route("locale", "") }}" id="locale-form-mobile" method="post">
                                    @csrf
                                    <select class="block appearance-none cursor-pointer py-1 rounded-md focus:outline-none" name="locale" id="locale-mobile" x-on:change="$el.form.submit()">
                                        @foreach ((config("app.settings.languages") ?? []) as $code => $language)
                                            @if (is_array($language) && ($language["is_active"] ?? false))
                                                <option {{ app()->getLocale() == $code ? "selected" : "" }} value="{{ $code }}">{{ $language["label"] ?? $code }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
