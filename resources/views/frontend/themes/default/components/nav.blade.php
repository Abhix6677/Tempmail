<nav class="w-full flex items-center justify-end select-none">
    
    {{-- Theme Toggle Button (Top Right) --}}
    <button 
        type="button"
        id="theme-toggle-btn"
        onclick="toggleTheme()"
        x-data="{
            isDark: document.documentElement.getAttribute('data-theme') === 'dark' || document.documentElement.classList.contains('dark')
        }"
        @tmail-theme-changed.window="isDark = $event.detail.isDark"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        title="Switch to light mode"
        class="w-[44px] h-[44px] rounded-[14px] flex items-center justify-center shadow-sm cursor-pointer transition-colors"
        style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border); color: var(--theme-text-secondary);"
    >
        {{-- Sun icon (visible when currently in DARK mode to switch to light) --}}
        <svg 
            class="theme-sun-icon h-5 w-5 text-yellow-400" 
            :style="isDark ? 'display: block;' : 'display: none;'"
            style="display: none;"
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor" 
            stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>

        {{-- Moon icon (visible when currently in LIGHT mode to switch to dark) --}}
        <svg 
            class="theme-moon-icon h-5 w-5 text-slate-700" 
            :style="!isDark ? 'display: block;' : 'display: none;'"
            style="display: block;"
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor" 
            stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>
</nav>
