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
<html dir="{{ config("app.settings.direction") }}" class="{{ config("app.settings.direction") }}" lang="{{ str_replace("_", "-", app()->getLocale()) }}" style="height:100%; background-color:#0f172a;">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        {{-- Page Title and Header --}}
        @if (isset($page))
            {!! $page->header !!}
            <title>{{ $page->title }} - {{ config("app.settings.name") }}</title>
        @elseif (isset($post))
            {!! $post->header !!}
            <title>{{ $post->title }} - {{ config("app.settings.name") }}</title>
        @else
            <title>{{ config("app.settings.name") }}</title>
        @endif

        {{-- Global Header --}}
        {!! config("app.settings.global.header") !!}

        {{-- Favicon Logic --}}

        @if (config("app.settings.favicon") && Illuminate\Support\Facades\Storage::disk("public")->has(config("app.settings.favicon")))
            <link rel="icon" href="{{ url("storage/" . config("app.settings.favicon")) }}" />
        @elseif (Illuminate\Support\Facades\Storage::disk("public")->has("images/custom-favicon.png"))
            <link rel="icon" href="{{ url("storage/images/custom-favicon.png") }}" type="image/png" />
        @else
            <link rel="icon" href="{{ asset("images/icon.png") }}" type="image/png" />
        @endif

        {{-- Font Awesome --}}
        <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" onload="this.onload=null;this.rel='stylesheet'" />

        {{-- Vite Assets --}}
        @vite(["resources/css/app.css", "resources/sass/common.scss", "resources/js/app.js"])

        {{-- Shortcode Script --}}
        <script src="{{ asset("vendor/Shortcode/Shortcode.js") }}"></script>

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
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                background-color: #0f172a; /* hard fallback to dark bg */
            }

            :root {
                --head-font: '{{ $headFont }}';
                --body-font: '{{ $bodyFont }}';
                --primary: {{ $primary }};
                --secondary: {{ $secondary }};
                --tertiary: {{ $tertiary }};
            }
        </style>

        {{-- Livewire Styles --}}
        @livewireStyles

        {{-- Global CSS --}}
        {!! config("app.settings.global.css") !!}

        {{-- App Header --}}
        @if (! isset($page) && ! isset($post))
            {!! config("app.settings.app_header") !!}
        @endif
    </head>
    <body class="h-full flex flex-col text-gray-200 bg-gray-900" style="background-color:#0f172a !important;">
        <div class="h-full flex flex-col bg-gray-900">
            {{-- Livewire Component --}}
            {{ $slot }}
        </div>

        {{-- Modals --}}
        @stack("modals")

        {{-- Cookie Policy --}}
        @if (config("app.settings.cookie.enable"))
            <div id="cookie" class="hidden fixed w-full bottom-0 left-0 p-4 bg-gray-900 text-white justify-between">
                <div class="py-2">
                    {!! __(config("app.settings.cookie.text")) !!}
                </div>
                <div id="cookie_close" class="px-3 py-2 bg-yellow-300 text-gray-900 rounded-md cursor-pointer">
                    {{ __("Close") }}
                </div>
            </div>
        @endif

        {{-- Language Helper --}}
        <div class="hidden language-helper">
            <div class="error">{{ __("Error") }}</div>
            <div class="success">{{ __("Success") }}</div>
            <div class="copy_text">{{ __("Email ID Copied to Clipboard") }}</div>
        </div>

        {{-- Livewire Scripts --}}
        @livewireScripts

        {{-- Inline Scripts --}}
        @if (! isset($page) && ! isset($post))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    try {
                        const email = '{{ App\Services\TMail::getEmail(true) }}' || '';
                        const add_mail_in_title = '{{ config("app.settings.add_mail_in_title") ? "yes" : "no" }}';
                        if (add_mail_in_title === 'yes') {
                            document.title += ` - ${email}`;
                        }
                        if (email) {
                            Livewire.dispatch('syncEmail', { email });
                        }
                        // Small delay to ensure Livewire components are mounted
                        setTimeout(() => {
                            Livewire.dispatch('fetchMessages');
                        }, 100);
                    } catch (e) {
                        console.warn('TMail: Error during initialization', e);
                    }
                });
            </script>
        @endif

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('stopLoader', () => {
                    setTimeout(() => {
                        if (document.getElementById('refresh')) {
                            document.getElementById('refresh').classList.add('pause-spinner');
                        }
                    }, 500);
                });
            });

            // Fetch interval with guard against stacking requests
            let fetchInProgress = false;
            let fetchSeconds = parseInt({{ config("app.settings.fetch_seconds", 30) }});
            let counter = isNaN(fetchSeconds) ? 30 : fetchSeconds;

            Livewire.on('fetchStarted', () => { fetchInProgress = true; });
            Livewire.on('fetchCompleted', () => { fetchInProgress = false; });

            setInterval(() => {
                // Safety: reset fetch guard if it's been stuck for more than 2 polling cycles
                if (fetchInProgress && counter <= -2) {
                    fetchInProgress = false;
                    console.warn('TMail: fetch guard auto-reset (stuck)');
                }

                if (counter <= 0 && document.getElementById('imap-error') === null && !document.hidden && !fetchInProgress) {
                    if (document.getElementById('refresh')) {
                        document.getElementById('refresh').classList.remove('pause-spinner');
                    }
                    fetchInProgress = true;
                    // Catch Livewire dispatch errors so fetch guard always resets
                    Livewire.dispatch('fetchMessages')
                        .catch(() => {
                            fetchInProgress = false;
                            console.warn('TMail: fetch dispatch failed, guard reset');
                        });
                    let fresh = parseInt({{ config("app.settings.fetch_seconds", 30) }});
                    counter = isNaN(fresh) ? 30 : fresh;
                } else {
                    counter--;
                }
                if (document.hidden) {
                    counter = 1;
                }
            }, 1000);
        </script>

        {{-- Captcha Configuration --}}
        <script>
            let captcha_name = '{{ config("app.settings.captcha", "off") }}';
            let site_key = '';
            if (captcha_name && captcha_name !== 'off') {
                site_key = '{{ config("app.settings." . config("app.settings.captcha") . ".site_key", "") }}';
            }
            let strings = {!! json_encode(\Lang::get("*")) !!};
            const __ = (string) => strings[string] ?? string;
        </script>

        {{-- Session Alerts --}}
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

        {{-- Ad Block Detector --}}
        @if (config("app.settings.enable_ad_block_detector"))
            <script>
                fetch('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js').catch(() => {
                    document.querySelector('[class*="-theme"]').remove();
                    document.querySelector('body > div').insertAdjacentHTML(
                        'beforebegin',
                        `
                        <div class="fixed w-screen h-screen bg-red-800 flex flex-col justify-center items-center gap-5 z-50 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-40 w-40" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                            </svg>
                            <h1 class="text-4xl font-bold">{{ __("Ad Blocker Detected") }}</h1>
                            <h2>{{ __("Disable the Ad Blocker to use ") . config("app.settings.name") }}</h2>
                        </div>
                        `
                    );
                });
            </script>
        @endif

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

        {{-- Global Scripts --}}
        {!! config("app.settings.global.js") !!}
        {!! config("app.settings.global.footer") !!}
    </body>
</html>
