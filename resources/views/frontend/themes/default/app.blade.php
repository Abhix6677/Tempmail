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
    <style>
        @media (min-width: 768px) {
            .tmail-app-layout {
                flex-direction: row !important;
            }
            .tmail-sidebar {
                width: 320px !important;
                min-width: 320px !important;
                max-width: 320px !important;
                height: 100% !important;
                border-right: 1px solid var(--theme-sidebar-border) !important;
                border-bottom: none !important;
            }
            .tmail-main {
                padding: 2.5rem !important;
            }
            .tmail-mobile-only {
                display: none !important;
            }
            .tmail-desktop-only {
                display: flex !important;
            }
        }
        @media (max-width: 767px) {
            .tmail-app-layout {
                flex-direction: column !important;
            }
            .tmail-sidebar {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                border-bottom: 1px solid var(--theme-sidebar-border) !important;
                border-right: none !important;
            }
            .tmail-main {
                padding: 0.75rem !important;
            }
            .tmail-mobile-only {
                display: flex !important;
            }
            .tmail-desktop-only {
                display: none !important;
            }
        }
    </style>

    <div class="tmail-clean-theme tmail-app-layout flex h-screen w-full overflow-hidden font-sans antialiased transition-colors duration-200" style="background-color: var(--theme-bg); color: var(--theme-text-primary);">
        
        {{-- Sidebar (Vertical on Desktop md+, Horizontal Top Bar on Mobile) --}}
        <aside class="tmail-sidebar flex flex-col shrink-0 z-20 select-none transition-colors duration-200" style="background-color: var(--theme-sidebar-bg);">
            
            {{-- Top Branding --}}
            <div class="px-4 py-3 md:px-6 md:pt-6 md:pb-4 flex items-center justify-between">
                <a href="{{ Util::localizeRoute("home") }}" class="flex items-center gap-2.5 md:gap-3 group">
                    <div class="w-9 h-9 md:w-10 md:h-10 bg-[#2563EB] rounded-xl flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-[1.02]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 md:h-5 md:w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                            <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[17px] md:text-[19px] font-bold tracking-tight leading-none mb-0.5 md:mb-1" style="color: var(--theme-text-primary);">{{ config("app.settings.name", "TMail") }}</div>
                        <div class="text-[11px] md:text-[12px] font-medium" style="color: var(--theme-text-secondary);">Temporary. Simple. Yours.</div>
                    </div>
                </a>

                {{-- Mobile Theme Toggle (Only visible on mobile) --}}
                <div class="tmail-mobile-only items-center">
                    <button
                        type="button"
                        onclick="toggleTheme()"
                        x-data="{
                            isDark: document.documentElement.getAttribute('data-theme') === 'dark' || document.documentElement.classList.contains('dark')
                        }"
                        @tmail-theme-changed.window="isDark = $event.detail.isDark"
                        class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm cursor-pointer transition-colors"
                        style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border); color: var(--theme-text-secondary);"
                        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                        title="Toggle theme"
                    >
                        <svg class="theme-sun-icon h-4.5 w-4.5 text-yellow-400" :style="isDark ? 'display: block;' : 'display: none;'" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg class="theme-moon-icon h-4.5 w-4.5 text-slate-700" :style="!isDark ? 'display: block;' : 'display: none;'" style="display: block;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Sidebar Navigation & Actions --}}
            <div class="px-3 pb-3 md:px-6 md:pb-6 flex-1 flex flex-col justify-between overflow-visible md:overflow-y-auto">
                @livewire("frontend.actions", ["in_app" => isset($page) || isset($category) || isset($post) || isset($profile) ? true : false])
            </div>

        </aside>

        {{-- Main Content Column --}}
        <main class="tmail-main flex-1 flex flex-col h-full min-w-0 min-h-0 overflow-hidden transition-colors duration-200" style="flex: 1 1 0% !important; min-width: 0 !important; background-color: var(--theme-bg);">
            
            {{-- Top Header (Desktop only) --}}
            <header class="tmail-desktop-only shrink-0 mb-8">
                @livewire("frontend.nav")
            </header>
            
            {{-- Main Dynamic Content Container --}}
            <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
                @if (isset($page))
                    <div class="max-w-4xl mx-auto w-full rounded-[24px] p-6 md:p-12 shadow-sm overflow-y-auto" style="background-color: var(--theme-bg); border: 1px solid var(--theme-surface-border);">
                        @livewire("frontend.page", ["page" => $page])
                    </div>
                @elseif (isset($post))
                    <div class="max-w-4xl mx-auto w-full rounded-[24px] p-6 md:p-12 shadow-sm overflow-y-auto" style="background-color: var(--theme-bg); border: 1px solid var(--theme-surface-border);">
                        @livewire("frontend.post", ["post" => $post])
                    </div>
                @elseif (isset($category))
                    <div class="max-w-4xl mx-auto w-full rounded-[24px] p-6 md:p-12 shadow-sm overflow-y-auto" style="background-color: var(--theme-bg); border: 1px solid var(--theme-surface-border);">
                        <h1 class="text-xl font-bold mb-4" style="color: var(--theme-text-primary);">{{ __("Category") }}: {{ $category->name }}</h1>
                        @include("frontend.common.posts", ["posts" => $posts])
                    </div>
                @elseif (isset($profile))
                    <div class="max-w-4xl mx-auto w-full rounded-[24px] p-6 md:p-12 shadow-sm overflow-y-auto" style="background-color: var(--theme-bg); border: 1px solid var(--theme-surface-border);">
                        @include("frontend.common.profile")
                    </div>
                @else
                    @livewire("frontend.app")
                @endif
            </div>

            {{-- Footer (Desktop only) --}}
            <footer class="tmail-desktop-only shrink-0 pt-8 text-[13px] flex-col sm:flex-row justify-between items-center gap-2 font-medium" style="color: var(--theme-text-secondary);">
                <div>&copy; {{ date("Y") }} {{ config("app.settings.name", "TMail") }}. Simple. Private. Open for Everyone.</div>
                <div class="flex items-center gap-1.5">
                    Built with <span class="text-red-500 text-sm">♥</span> for a simpler internet.
                </div>
            </footer>
        </main>
    </div>
</x-frontend-layout>
