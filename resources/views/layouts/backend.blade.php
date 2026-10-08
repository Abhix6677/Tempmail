<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
                <meta name="csrf-token" content="{{ csrf_token() }}" />

        <script>
            function getPreferredTheme() {
                try {
                    const tmailTheme = localStorage.getItem('tmail-theme');
                    if (tmailTheme === 'dark') return 'dark';
                    if (tmailTheme === 'light') return 'light';
                    const dm = localStorage.getItem('darkmode');
                    if (dm === 'enabled') return 'dark';
                    if (dm === 'disabled') return 'light';
                } catch (e) {}
                return 'light'; // Strictly default to light mode
            }

            (function() {
                const theme = getPreferredTheme();
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-mode', theme);
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                }
            })();
        </script>

        @hasSection("title")
            <title>@yield("title") - {{ config("app.settings.name", "TMail") }}</title>
        @else
            <title>{{ config("app.settings.name", "TMail") }}</title>
        @endif
        <link rel="shortcut icon" href="{{ asset("images/icon.png") }}" type="image/png" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://use.hugeicons.com/font/icons.css" />
        <!-- Scripts -->
        @vite(["resources/css/app.css", "resources/js/app.js"])

        <!-- Styles -->
        @livewireStyles
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.9.1/tinymce.min.js" integrity="sha512-09JpfVm/UE1F4k8kcVUooRJAxVMSfw/NIslGlWE/FGXb2uRO1Nt4BXAJ3LxPqNbO3Hccdu46qaBPp9wVpWAVhA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-banner />

        <div class="flex flex-col min-h-screen">
            <div class="flex-1">
                @livewire("navigation-menu")

                                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white shadow">
                        <div class="w-full px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @elseif (View::hasSection('header'))
                    <header class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white shadow">
                        <div class="w-full px-4 sm:px-6 lg:px-8">
                            @yield('header')
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    @if (isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </main>
            </div>
            <footer class="bg-gray-900 dark:bg-gray-800 text-white shadow mt-6">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col-reverse md:flex-row justify-between items-center gap-5">
                        <div class="text-sm">{{ __("Powered by TMail") }} v{{ config("app.settings.version") }}</div>
                        <div class="flex gap-5 text-sm">
                            <a class="border-b border-transparent hover:border-gray-100" href="https://tmail.hp.gl/docs/" target="_blank" rel="noopener noreferrer">{{ __("Documentation") }}</a>
                            <a class="border-b border-transparent hover:border-gray-100" href="https://helpdesk.thehp.in" target="_blank" rel="noopener noreferrer">{{ __("Contact Support") }}</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Floating View Website Button -->
        <a id="view-website-btn" href="{{ route("home") }}" target="_blank" class="block fixed bottom-4 left-1/2 -translate-x-1/2 opacity-0 translate-y-10 scale-95 pointer-events-none transition-all duration-500 ease-out">
            <x-button-icon style="primary" icon="hgi-link-square-01 ml-2">
                {{ __("View Website") }}
            </x-button-icon>
        </a>

        @stack("modals")

        @livewireScripts

        <script>
            function enableDarkMode() {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.setAttribute('data-mode', 'dark');
                document.documentElement.setAttribute('data-theme', 'dark');
                try {
                    localStorage.setItem('darkmode', 'enabled');
                    localStorage.setItem('tmail-theme', 'dark');
                } catch (e) {}
                window.dispatchEvent(new CustomEvent('darkmode-changed', { detail: { darkmode: true } }));
                window.dispatchEvent(new CustomEvent('tmail-theme-changed', { detail: { theme: 'dark', isDark: true } }));
            }
            function disableDarkMode() {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                document.documentElement.setAttribute('data-mode', 'light');
                document.documentElement.setAttribute('data-theme', 'light');
                try {
                    localStorage.setItem('darkmode', 'disabled');
                    localStorage.setItem('tmail-theme', 'light');
                } catch (e) {}
                window.dispatchEvent(new CustomEvent('darkmode-changed', { detail: { darkmode: false } }));
                window.dispatchEvent(new CustomEvent('tmail-theme-changed', { detail: { theme: 'light', isDark: false } }));
            }
            function toggleDarkMode() {
                if (document.documentElement.classList.contains('dark')) {
                    disableDarkMode();
                    return false;
                } else {
                    enableDarkMode();
                    return true;
                }
            }
            window.enableDarkMode = enableDarkMode;
            window.disableDarkMode = disableDarkMode;
            window.toggleDarkMode = toggleDarkMode;

            document.addEventListener('DOMContentLoaded', () => {
                const theme = (typeof getPreferredTheme === 'function') ? getPreferredTheme() : 'light';
                if (theme === 'dark') {
                    enableDarkMode();
                } else {
                    disableDarkMode();
                }
            });
        </script>
    </body>
</html>
