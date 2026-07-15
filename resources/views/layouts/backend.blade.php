<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

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
                @hasSection('header')
                    <header class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            @yield('header')
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    @yield('content')
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

        @if (auth()->check() && auth()->user()->role == 7)
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const darkmode = localStorage.getItem('darkmode');
                    if (darkmode && darkmode == 'enabled') {
                        enableDarkMode();
                    } else if (!darkmode && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        enableDarkMode();
                    } else {
                        disableDarkMode();
                    }
                });
                function enableDarkMode() {
                    document.documentElement.setAttribute('data-mode', 'dark');
                    localStorage.setItem('darkmode', 'enabled');
                }
                function disableDarkMode() {
                    document.documentElement.setAttribute('data-mode', 'light');
                    localStorage.setItem('darkmode', 'disabled');
                }
            </script>
        @endif
    </body>
</html>
