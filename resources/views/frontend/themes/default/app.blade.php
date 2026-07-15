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
    <div class="default-theme flex flex-col h-full bg-gray-900">
        <div class="flex flex-1 h-full min-h-0 flex-col md:flex-row">
            <div class="w-full lg:w-1/4 bg-blue-700 py-6 flex flex-col h-full" style="background-color: {{ config("app.settings.colors.primary") }}">
                <div class="flex px-8 lg:px-0 lg:justify-center p-3 mb-10">
                    <a href="{{ Util::localizeRoute("home") }}">
                        @if (config("app.settings.logo") && Illuminate\Support\Facades\Storage::disk("public")->has(config("app.settings.logo")))
                            <img class="max-w-40" src="{{ url("storage/" . config("app.settings.logo")) }}" alt="logo" />
                        @elseif (Illuminate\Support\Facades\Storage::disk("public")->has("images/custom-logo.png"))
                            <img class="max-w-40" src="{{ url("storage/images/custom-logo.png") }}" alt="logo" />
                        @else
                            <img class="max-w-40" src="{{ asset("images/logo.png") }}" alt="logo" />
                        @endif
                    </a>
                </div>
                @if (config("app.settings.ads.five"))
                    <div class="flex justify-center items-center max-w-full m-4 adz-five">{!! config("app.settings.ads.five") !!}</div>
                @endif

                @livewire("frontend.actions", ["in_app" => isset($page) || isset($category) || isset($post) || isset($profile) ? true : false])
                @if (config("app.settings.ads.one"))
                    <div class="flex justify-center items-center max-w-full m-4 adz-one">{!! config("app.settings.ads.one") !!}</div>
                @endif
            </div>
            <div class="flex-1 w-full flex flex-col lg:w-3/4">
                @livewire("frontend.nav")
                <div class="flex-1 flex flex-col min-h-0">
                    @if (config("app.settings.ads.two"))
                        <div class="flex justify-center items-center max-w-full adz-two">{!! config("app.settings.ads.two") !!}</div>
                    @endif

                    @if (isset($page))
                        @livewire("frontend.page", ["page" => $page])
                    @elseif (isset($post))
                        @livewire("frontend.post", ["post" => $post])
                    @elseif (isset($category))
                        <main class="category flex-1 p-5">
                            <h1 class="text-xl font-bold">{{ __("Category") }}: {{ $category->name }}</h1>
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
                <footer class="mt-auto bg-gray-800 px-6 py-4 text-white text-sm">
                    <div class="flex items-center justify-center lg:hidden mb-5">
                        @foreach (config("app.settings.socials", []) as $social)
                            <a href="{{ $social["link"] }}" target="_blank" class="ml-2 text-lg text-gray-200" rel="noopener noreferrer"><i class="{{ $social["icon"] }}"></i></a>
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
                </footer>
            </div>
        </div>
    </div>
</x-frontend-layout>
