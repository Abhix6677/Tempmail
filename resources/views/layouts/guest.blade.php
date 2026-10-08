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
            }
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
