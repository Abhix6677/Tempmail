<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        {{-- Page Title and Header --}}
        <title>{{ config("app.settings.name") }}</title>

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

        {{-- Google Fonts --}}
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(" ", "+", config("app.settings.font_family.head", "Poppins")) }}:wght@400;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" />
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(" ", "+", config("app.settings.font_family.body", "Poppins")) }}:wght@400;600&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" />

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

        {{-- Global CSS --}}
        {!! config("app.settings.global.css") !!}
    </head>
    <body>
        <div class="min-h-screen bg-primary">
            <div class="container mx-auto">
                <div class="flex h-screen">
                    <div class="m-auto">
                        <div class="flex justify-center my-10">
                            @if (config("app.settings.logo") && Illuminate\Support\Facades\Storage::disk("local")->has(config("app.settings.logo")))
                                <img class="w-56" src="{{ url("storage/" . config("app.settings.logo")) }}" alt="logo" />
                            @elseif (Illuminate\Support\Facades\Storage::disk("local")->has("public/images/custom-logo.png"))
                                <img class="max-w-56" src="{{ url("storage/images/custom-logo.png") }}" alt="logo" />
                            @else
                                <img class="max-w-56" src="{{ asset("images/logo.png") }}" alt="logo" />
                            @endif
                        </div>
                        <div class="bg-white rounded-2xl p-10 min-w-96 max-w-screen">
                            @if (Session::has("error"))
                                <div class="bg-red-50 w-full flex justify-between items-center rounded-lg px-4 py-3 text-sm mb-5">
                                    <div class="flex justify-start items-center space-x-3">
                                        <div class="text-red-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-red-400 font-medium">{{ Session::get("error") }}</p>
                                    </div>
                                </div>
                            @endif

                            {!! config("app.settings.lock.text") !!}
                            <form action="{{ route("unlock") }}" class="flex justify-center items-center gap-2" method="post">
                                @csrf
                                <input type="password" name="password" id="password" class="flex-1 w-full rounded-md px-4 py-2 text-sm outline-none border-1 border-gray-200 focus:border-gray-200 focus:shadow-none" placeholder="{{ __("Password") }}" />
                                <button type="submit" class="rounded-md px-4 py-2 text-sm border-1 text-white" style="border-color: {{ config("app.settings.colors.secondary") }}; background-color: {{ config("app.settings.colors.secondary") }}">{{ __("Unlock") }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

        {{-- Global Scripts --}}
        {!! config("app.settings.global.js") !!}
        {!! config("app.settings.global.footer") !!}
    </body>
</html>
