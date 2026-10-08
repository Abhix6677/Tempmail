@props([
    "content" => null,
])
@php
    if (isset($content["page"])) {
        $page = $content["page"];
    }
    if (isset($content["post"])) {
        $post = $content["post"];
    }
@endphp

<!DOCTYPE html>
<html dir="{{ config("app.settings.direction", "ltr") }}" lang="{{ str_replace("_", "-", app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        {{-- Page Title --}}
        @if (isset($page))
            {!! $page->header !!}
            <title>{{ $page->title }} - {{ config("app.settings.name", "TMail") }}</title>
        @elseif (isset($post))
            {!! $post->header !!}
            <title>{{ $post->title }} - {{ config("app.settings.name", "TMail") }}</title>
        @else
            <title>{{ config("app.settings.name", "TMail") }}</title>
        @endif

        {{-- Favicon --}}
        @if (config("app.settings.favicon") && Illuminate\Support\Facades\Storage::disk("public")->has(config("app.settings.favicon")))
            <link rel="icon" href="{{ url("storage/" . config("app.settings.favicon")) }}" />
        @elseif (Illuminate\Support\Facades\Storage::disk("public")->has("images/custom-favicon.png"))
            <link rel="icon" href="{{ url("storage/images/custom-favicon.png") }}" type="image/png" />
        @else
            <link rel="icon" href="{{ asset("images/icon.png") }}" type="image/png" />
        @endif

        {{-- Font Awesome --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />

        {{-- Google / Bunny Fonts - Inter --}}
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

        {{-- Vite Assets --}}
        @vite(["resources/css/app.css", "resources/sass/common.scss", "resources/js/app.js"])

        {{-- Shortcode Script --}}
        <script src="{{ asset("vendor/Shortcode/Shortcode.js") }}"></script>

        {{-- Theme Initialization & Controller --}}
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

            function applyTheme(theme) {
                const isDark = theme === 'dark';
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-mode', theme);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                }
                try {
                    localStorage.setItem('tmail-theme', theme);
                    localStorage.setItem('darkmode', isDark ? 'enabled' : 'disabled');
                } catch (e) {}

                // Immediately update all theme button icons and titles
                document.querySelectorAll('.theme-sun-icon').forEach(function(el) {
                    el.style.display = isDark ? 'block' : 'none';
                });
                document.querySelectorAll('.theme-moon-icon').forEach(function(el) {
                    el.style.display = isDark ? 'none' : 'block';
                });
                const btn = document.getElementById('theme-toggle-btn');
                if (btn) {
                    btn.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                    btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                }

                window.dispatchEvent(new CustomEvent('tmail-theme-changed', { detail: { theme: theme, isDark: isDark } }));
                window.dispatchEvent(new CustomEvent('darkmode-changed', { detail: { darkmode: isDark } }));
            }

            function toggleTheme() {
                const current = document.documentElement.getAttribute('data-theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                const next = current === 'dark' ? 'light' : 'dark';
                applyTheme(next);
                return next;
            }

            window.getPreferredTheme = getPreferredTheme;
            window.toggleTheme = toggleTheme;
            window.applyTheme = applyTheme;
            window.enableDarkMode = function() { applyTheme('dark'); };
            window.disableDarkMode = function() { applyTheme('light'); };

            document.addEventListener('DOMContentLoaded', () => {
                applyTheme(getPreferredTheme());
            });
        </script>

        <style>
            [x-cloak] { display: none !important; }
            :root, :root[data-theme="dark"], html.dark, html[data-mode="dark"] {
                --theme-bg: #0F172A;
                --theme-sidebar-bg: #0F172A;
                --theme-sidebar-border: #1E293B;
                --theme-surface: #1E293B;
                --theme-surface-border: #334155;
                --theme-surface-header: #131B2E;
                --theme-text-primary: #F8FAFC;
                --theme-text-secondary: #94A3B8;
                --theme-text-muted: #64748B;
                --theme-inbox-bg: rgba(37, 99, 235, 0.2);
                --theme-inbox-text: #60A5FA;
                --theme-item-hover: #1E293B;
                --theme-account-bg: #1E293B;
                --theme-account-border: #334155;
                --theme-account-avatar-bg: rgba(37, 99, 235, 0.25);
                --theme-account-avatar-text: #93C5FD;
                --theme-button-secondary-bg: #0F172A;
                --theme-button-secondary-border: #334155;
                --theme-button-secondary-text: #F8FAFC;
                --theme-button-secondary-hover: #1E293B;
                --theme-input-bg: #0F172A;
                --theme-input-border: #334155;
                --theme-input-text: #F8FAFC;
                --theme-illustr-circle-1: #1E293B;
                --theme-illustr-circle-2: #162032;
                --theme-illustr-env-back: #334155;
                --theme-illustr-env-front: #475569;
                --theme-illustr-env-border: #64748B;
                --theme-illustr-letter: #1E293B;
                --theme-illustr-letter-border: #475569;
                --theme-illustr-line-1: #64748B;
                --theme-illustr-line-2: #475569;
            }

            :root[data-theme="light"], html.light, html[data-mode="light"] {
                --theme-bg: #E2E8F0;
                --theme-sidebar-bg: #E2E8F0;
                --theme-sidebar-border: #CBD5E1;
                --theme-surface: #FFFFFF;
                --theme-surface-border: #CBD5E1;
                --theme-surface-header: #F1F5F9;
                --theme-text-primary: #0F172A;
                --theme-text-secondary: #475569;
                --theme-text-muted: #64748B;
                --theme-inbox-bg: #FFFFFF;
                --theme-inbox-text: #2563EB;
                --theme-item-hover: #CBD5E1;
                --theme-account-bg: #FFFFFF;
                --theme-account-border: #CBD5E1;
                --theme-account-avatar-bg: #EFF6FF;
                --theme-account-avatar-text: #2563EB;
                --theme-button-secondary-bg: #FFFFFF;
                --theme-button-secondary-border: #CBD5E1;
                --theme-button-secondary-text: #0F172A;
                --theme-button-secondary-hover: #F8FAFC;
                --theme-input-bg: #FFFFFF;
                --theme-input-border: #94A3B8;
                --theme-input-text: #0F172A;
                --theme-illustr-circle-1: #EFF6FF;
                --theme-illustr-circle-2: #DBEAFE;
                --theme-illustr-env-back: #BFDBFE;
                --theme-illustr-env-front: #93C5FD;
                --theme-illustr-env-border: #3B82F6;
                --theme-illustr-letter: #FFFFFF;
                --theme-illustr-letter-border: #CBD5E1;
                --theme-illustr-line-1: #475569;
                --theme-illustr-line-2: #64748B;
            }

            * {
                box-sizing: border-box;
                transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
            }
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background-color: var(--theme-bg) !important;
                color: var(--theme-text-primary) !important;
                overflow: hidden;
            }

            aside svg {
                width: 18px !important;
                height: 18px !important;
                max-width: 18px !important;
                max-height: 18px !important;
                flex-shrink: 0 !important;
            }
            aside svg[style*="display: none"], aside [style*="display: none"], aside [x-cloak] {
                display: none !important;
            }
            aside a[href*="home"] svg, aside .brand-logo svg {
                width: 20px !important;
                height: 20px !important;
                max-width: 20px !important;
                max-height: 20px !important;
            }
            aside button[class*="New Mailbox"] svg, aside button:first-child svg {
                width: 16px !important;
                height: 16px !important;
                max-width: 16px !important;
                max-height: 16px !important;
            }

            @keyframes spinAnimation {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }
            .animate-spin {
                animation: spinAnimation 0.8s linear infinite !important;
            }
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }
            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>

        {{-- Livewire Styles --}}
        @livewireStyles

        {!! config("app.settings.global.header") !!}
        {!! config("app.settings.global.css") !!}
    </head>
    <body class="h-full w-full antialiased overflow-hidden font-sans">
        
        {{-- App Container --}}
        <div class="h-full w-full flex flex-col overflow-hidden">
            {{ $slot }}
        </div>

        {{-- Modals Stack --}}
        @stack("modals")

        {{-- Livewire Scripts --}}
        @livewireScripts

        {{-- TimeAgo & Auto-Fetch Polling --}}
        <script>
            window.formatTimeAgo = function(timestamp) {
                if (!timestamp) return '';
                const then = new Date(timestamp);
                const now = new Date();
                const diffInSec = Math.max(0, Math.floor((now - then) / 1000));
                if (diffInSec < 45) return 'just now';
                if (diffInSec < 90) return '1 minute ago';
                const mins = Math.floor(diffInSec / 60);
                if (mins < 45) return mins + ' minutes ago';
                if (mins < 90) return '1 hour ago';
                const hours = Math.floor(diffInSec / 3600);
                if (hours < 22) return hours + ' hours ago';
                if (hours < 36) return '1 day ago';
                const days = Math.floor(diffInSec / 86400);
                if (days < 26) return days + ' days ago';
                if (days < 45) return '1 month ago';
                const months = Math.floor(days / 30);
                if (months < 11) return months + ' months ago';
                if (months < 18) return '1 year ago';
                const years = Math.floor(days / 365);
                return years + ' years ago';
            };
        </script>
        @if (! isset($page) && ! isset($post))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    try {
                        const email = '{{ App\Services\TMail::getEmail(true) }}' || '';
                        if (email) {
                            Livewire.dispatch('syncEmail', { email });
                        }
                        @php
                            $currentEmail = App\Services\TMail::getEmail();
                            $hasCached = $currentEmail ? (!empty(Illuminate\Support\Facades\Cache::get('tmail_inbox_' . md5($currentEmail), [])) || !empty(session()->get('tmail_messages_' . $currentEmail, []))) : false;
                        @endphp
                        const hasCached = {{ $hasCached ? 'true' : 'false' }};
                        if (!hasCached) {
                            setTimeout(() => {
                                Livewire.dispatch('fetchMessages');
                            }, 500);
                        }

                        // Background auto-fetch polling
                        let fetchInProgress = false;
                        const fetchInterval = parseInt({{ config('app.settings.fetch_seconds', 20) }}) || 20;
                        let counter = fetchInterval;

                        if (window.Livewire) {
                            Livewire.on('stopLoader', () => { fetchInProgress = false; });
                            Livewire.on('fetchCompleted', () => { fetchInProgress = false; });
                        }

                        setInterval(() => {
                            if (document.hidden) {
                                counter = 2;
                                return;
                            }
                            if (counter <= 0) {
                                if (!fetchInProgress && document.getElementById('imap-error') === null) {
                                    fetchInProgress = true;
                                    Livewire.dispatch('fetchMessages');
                                    setTimeout(() => { fetchInProgress = false; }, 12000);
                                }
                                counter = fetchInterval;
                            } else {
                                counter--;
                            }
                        }, 1000);
                    } catch (e) {
                        console.warn('TMail init:', e);
                    }
                });
            </script>
        @endif

        {{-- Toast / Alert Handling --}}
        @foreach (["success", "error"] as $type)
            @if (Session::has($type))
                <script defer>
                    document.addEventListener('DOMContentLoaded', () => {
                        document.dispatchEvent(
                            new CustomEvent('showAlert', {
                                bubbles: true,
                                detail: {
                                    type: '{{ $type }}',
                                    message: '{{ Session::get($type) }}',
                                },
                            })
                        );
                    });
                </script>
            @endif
        @endforeach

        {!! config("app.settings.global.js") !!}
        {!! config("app.settings.global.footer") !!}
    </body>
</html>
