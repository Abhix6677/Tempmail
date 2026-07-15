@php
    $content = [];
    if (isset($page)) {
        $content["page"] = $page;
    }
    if (isset($post)) {
        $content["post"] = $post;
    }
@endphp

<x-frontend-layout :content="$content">
    <div class="nebula-theme min-h-screen flex flex-col">
        <header class="bg-blue-700 dark:bg-blue-900 text-white dark:text-gray-100 order-1" style="background-color: {{ config("app.settings.colors.primary") }}">
            <div class="container mx-auto pb-24">
                <div class="flex flex-wrap items-center">
                    <a class="px-3 py-5 ml-5 lg:ml-0" href="{{ Util::localizeRoute("home") }}">
                        @if (config("app.settings.logo") && Illuminate\Support\Facades\Storage::disk("local")->has(config("app.settings.logo")))
                            <img class="max-w-40" src="{{ url("storage/" . config("app.settings.logo")) }}" alt="logo" />
                        @elseif (Illuminate\Support\Facades\Storage::disk("local")->has("public/images/custom-logo.png"))
                            <img class="max-w-40" src="{{ url("storage/images/custom-logo.png") }}" alt="logo" />
                        @else
                            <img class="max-w-40" src="{{ asset("images/logo.png") }}" alt="logo" />
                        @endif
                    </a>
                    <div class="flex-1">
                        @livewire("frontend.nav")
                    </div>
                </div>
                @if (config("app.settings.ads.five"))
                    <div class="flex justify-center items-center max-w-full m-4 adz-five">{!! config("app.settings.ads.five") !!}</div>
                @endif

                <div class="actions">
                    @livewire("frontend.actions", ["in_app" => isset($page) || isset($category) || isset($post) || isset($profile) ? true : false])
                </div>
                @if (config("app.settings.ads.one"))
                    <div class="flex justify-center items-center max-w-full m-4 adz-one">{!! config("app.settings.ads.one") !!}</div>
                @endif
            </div>
        </header>
        <div class="grow container mx-auto order-2 bg-white dark:bg-gray-900 md:rounded-md shadow-md flex flex-col md:flex-row md:space-x-2 justify-center z-10 -mt-16 -mb-16">
            @if (config("app.settings.ads.two"))
                <div class="flex justify-center items-center max-w-full adz-two">{!! config("app.settings.ads.two") !!}</div>
            @endif

            @if (isset($page))
                @livewire("frontend.page", ["page" => $page])
            @elseif (isset($post))
                @livewire("frontend.post", ["post" => $post])
            @elseif (isset($category))
                <main class="category flex-1 p-5">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __("Category") }}: {{ $category->name }}</h1>
                    @include("frontend.common.posts", ["posts" => $posts])
                </main>
            @elseif (isset($profile))
                @include("frontend.common.profile")
            @else
                @livewire("frontend.app")
            @endif
            @if (config("app.settings.ads.three"))
                <div class="flex justify-center items-center max-w-full adz-three">{!! config("app.settings.ads.three") !!}</div>
            @endif
        </div>
        <footer class="bg-gray-800 order-3 text-white text-sm pt-20 pb-6 z-0">
            <div class="container mx-auto">
                <div class="flex items-center justify-center lg:hidden mb-5">
                    @foreach (config("app.settings.socials", []) as $social)
                        <a href="{{ $social["link"] }}" target="_blank" class="ml-2 text-lg text-gray-200 dark:text-gray-300 hover:text-white dark:hover:text-gray-100" rel="noopener noreferrer"><i class="{{ $social["icon"] }}"></i></a>
                    @endforeach
                </div>
                <div class="flex flex-col lg:flex-row gap-5 justify-between items-center">
                    <div class="flex space-x-3">
                        @foreach (\App\Models\Menu::where("status", true)->where("location", "secondary")->orderBy("order")->get() as $menu)
                            <a href="{{ Util::localizeUrl($menu->link) }}" class="text-white dark:text-gray-100 hover:underline dark:hover:text-gray-300">
                                {{ $menu->name }}
                            </a>
                            @if (! $loop->last)
                                <span class="opacity-50 dark:opacity-60">•</span>
                            @endif
                        @endforeach
                    </div>
                    <div class="opacity-75 dark:opacity-80 text-white dark:text-gray-200">{{ __("Copyright") }} &copy; {{ date("Y") }} {{ config("app.settings.name") }}. {{ __("All rights reserved.") }}</div>
                    <div class="hidden lg:block">
                        @foreach (config("app.settings.socials", []) as $social)
                            <a href="{{ $social["link"] }}" target="_blank" class="ml-2 text-lg text-gray-200 dark:text-gray-300 hover:text-white dark:hover:text-gray-100" rel="noopener noreferrer"><i class="{{ $social["icon"] }}"></i></a>
                        @endforeach
                    </div>
                </div>
            </div>
        </footer>
    </div>
</x-frontend-layout>
