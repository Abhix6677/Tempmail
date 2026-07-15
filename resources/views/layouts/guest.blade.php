<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>@yield("title", config("app.settings.name", "TMail"))</title>
        <link rel="shortcut icon" href="{{ asset("images/icon.png") }}" type="image/png" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://use.hugeicons.com/font/icons.css" />
        <!-- Scripts -->
        @vite(["resources/css/app.css", "resources/sass/common.scss", "resources/js/app.js"])

        {{-- Google Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css2?family={{ str_replace(" ", "+", config("app.settings.font_family.head", "Poppins")) }}:wght@400;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" />
        <link href="https://fonts.bunny.net/css2?family={{ str_replace(" ", "+", config("app.settings.font_family.body", "Poppins")) }}:wght@400;600&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" />

        {{-- CSS Variables --}}
        @php
            $headFont = config("app.settings.font_family.head", "Poppins");
            $bodyFont = config("app.settings.font_family.body", "Poppins");
            $primary = config("app.settings.colors.primary", "#0155b5");
            $secondary = config("app.settings.colors.secondary", "#2fc10a");
            $tertiary = config("app.settings.colors.tertiary", "#d2ab3e");
        @endphp

        <style>
            :root {
                --head-font: '{{ $headFont }}';
                --body-font: '{{ $bodyFont }}';
                --primary: {{ $primary }};
                --secondary: {{ $secondary }};
                --tertiary: {{ $tertiary }};
            }
        </style>

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <div class="min-h-screen">
            {{ $slot }}
        </div>

        @livewireScripts

        @if (config("app.settings.enable_dark_mode"))
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
