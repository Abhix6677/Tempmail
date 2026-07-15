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
    <div class="mantis-theme flex flex-col min-h-screen">
        <div class="container mx-auto p-5 md:p-0">
            <div class="flex items-center">
                <a href="{{ Util::localizeRoute("home") }}">
                    @if (config("app.settings.logo") && Illuminate\Support\Facades\Storage::disk("local")->has(config("app.settings.logo")))
                        <img class="max-w-40" src="{{ url("storage/" . config("app.settings.logo")) }}" alt="logo" />
                    @elseif (Illuminate\Support\Facades\Storage::disk("local")->has("public/images/custom-logo.png"))
                        <img class="max-w-40" src="{{ url("storage/images/custom-logo.png") }}" alt="logo" />
                    @else
                        <img class="max-w-40" src="{{ asset("images/logo.png") }}" alt="logo" />
                    @endif
                </a>
                @livewire("frontend.nav")
            </div>
        </div>
        @livewire("frontend.actions", ["in_app" => isset($page) || isset($category) || isset($post) || isset($profile) ? true : false])
        <div class="container mx-auto flex-1 flex flex-col">
            @if (config("app.settings.ads.two"))
                <div class="flex justify-center items-center max-w-full m-4 adz-two">{!! config("app.settings.ads.two") !!}</div>
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
                <div class="spacer mt-5"></div>
                @livewire("frontend.app")
                <div class="spacer mb-5"></div>
            @endif
            @if (config("app.settings.ads.three"))
                <div class="flex justify-center items-center max-w-full m-4 adz-three">{!! config("app.settings.ads.three") !!}</div>
            @endif
        </div>
        <footer class="bg-gray-800 px-6 py-4 text-white text-sm">
            <div class="container mx-auto">
                <div class="flex items-center justify-center lg:hidden mb-5">
                    @foreach (config("app.settings.socials", []) as $social)
                        <a href="{{ $social["link"] }}" target="_blank" class="ml-2 text-lg text-gray-200 dark:text-gray-300 hover:text-white dark:hover:text-gray-100" rel="noopener noreferrer"><i class="{{ $social["icon"] }}"></i></a>
                    @endforeach
                </div>
                <div class="flex flex-col lg:flex-row gap-5 justify-between items-center">
                    <div class="flex space-x-3">
                        @foreach (\App\Models\Menu::where("status", true)->where("location", "secondary")->orderBy("order")->get() as $menu)
                            <a href="{{ Util::localizeUrl($menu->link) }}" class="text-white hover:underline">
                                {{ $menu->name }}
                            </a>
                            @if (! $loop->last)
                                <span class="opacity-50">•</span>
                            @endif
                        @endforeach
                    </div>
                    <div class="opacity-75">{{ __("Copyright") }} &copy; {{ date("Y") }} {{ config("app.settings.name") }}. {{ __("All rights reserved.") }}</div>
                    <div class="hidden lg:block">
                        @foreach (config("app.settings.socials", []) as $social)
                            <a href="{{ $social["link"] }}" target="_blank" class="ml-2 text-lg text-gray-200" rel="noopener noreferrer"><i class="{{ $social["icon"] }}"></i></a>
                        @endforeach
                    </div>
                </div>
            </div>
        </footer>
    </div>
</x-frontend-layout>

<style>
    .bg-actions-pattern {
        background-image: url('{{ asset("themes/mantis/images/bg-actions-pattern.png") }}');
    }
</style>
