<div 
    x-data="{ 
        showModal: false, 
        showDeleteConfirm: false,
        openAccount: false,
        copied: false,
        refreshing: false,
        in_app: {{ $in_app ? "true" : "false" }} 
    }" 
    @open-new-mailbox.window="showModal = true"
    @keydown.escape.window="showModal = false; showDeleteConfirm = false; openAccount = false"
    x-on:stop-loader.window="refreshing = false"
    class="flex flex-col h-full justify-between select-none w-full"
>
    
    {{-- ==================== MOBILE HORIZONTAL BAR (< md) ==================== --}}
    <div class="tmail-mobile-only flex-col gap-2.5 w-full select-none">
        
        {{-- Row 1: Active Mailbox Account Card --}}
        <div class="relative w-full">
            <div
                @click="openAccount = !openAccount"
                class="w-full h-[42px] flex items-center justify-between px-3 rounded-xl transition-all shadow-xs cursor-pointer select-none"
                style="background-color: var(--theme-account-bg); border: 1px solid var(--theme-account-border); color: var(--theme-text-primary);"
            >
                <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                    <div
                        class="w-7.5 h-7.5 rounded-lg flex items-center justify-center font-bold text-[12.5px] shrink-0 shadow-xs"
                        style="background-color: var(--theme-account-avatar-bg); color: var(--theme-account-avatar-text);"
                    >
                        {{ strtoupper(substr($email ?: 'A', 0, 1)) }}
                    </div>
                    <span class="text-[13px] font-semibold truncate leading-none min-w-0 flex-1 tracking-tight" style="color: var(--theme-text-primary);" title="{{ $email }}">
                        {{ $email ?: __("Generating...") }}
                    </span>
                </div>

                <div class="shrink-0 transition-transform duration-150" :class="{ 'rotate-180': openAccount }" style="color: var(--theme-text-secondary);">
                    <svg width="15" height="15" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            {{-- Mobile Switch Mailbox Dropdown (Opens downwards underneath the card) --}}
            <div
                x-show="openAccount"
                @click.away="openAccount = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute top-full left-0 w-full mt-1.5 rounded-xl shadow-2xl overflow-hidden z-50 p-1.5"
                style="display: none; background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border);"
            >
                <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wider border-b flex items-center justify-between" style="color: var(--theme-text-muted); border-color: var(--theme-surface-border);">
                    <span>Switch Mailbox</span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 font-mono">{{ count($emails) }}/3 active</span>
                </div>
                <div class="max-h-48 overflow-y-auto py-1">
                    @forelse ($emails as $em)
                        <a
                            href="{{ route('switch', $em) }}"
                            class="flex items-center justify-between px-3 py-2 rounded-lg text-[13px] font-medium transition-colors"
                            @if ($em === $email)
                                style="background-color: var(--theme-inbox-bg); color: var(--theme-inbox-text);"
                            @else
                                style="color: var(--theme-text-primary);"
                                onmouseover="this.style.backgroundColor='var(--theme-item-hover)'"
                                onmouseout="this.style.backgroundColor='transparent'"
                            @endif
                        >
                            <span class="truncate">{{ $em }}</span>
                            @if ($em === $email)
                                <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] shrink-0"></span>
                            @endif
                        </a>
                    @empty
                        <div class="px-3 py-2 text-xs text-center" style="color: var(--theme-text-muted);">No other accounts</div>
                    @endforelse
                </div>
            </div>
        </div>

                {{-- Row 2: 2-per-row Action Grid for Mobile --}}
        <div class="grid grid-cols-2 gap-2 w-full py-0.5">
            
            {{-- 1. New Mailbox Button --}}
            <button
                type="button"
                @click="showModal = true"
                class="h-[40px] w-full flex items-center justify-center gap-2 px-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.98] text-white font-semibold text-[13px] transition-all cursor-pointer shadow-sm"
            >
                <svg width="15" height="15" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span class="truncate">New</span>
            </button>

            {{-- 2. Inbox (Active) --}}
            <a
                href="{{ Util::localizeRoute("mailbox") }}"
                class="h-[40px] w-full flex items-center justify-center gap-2 px-3 rounded-xl font-semibold text-[13px] transition-all shadow-xs"
                style="background-color: var(--theme-inbox-bg); color: var(--theme-inbox-text); border: 1px solid var(--theme-surface-border);"
            >
                <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                    <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                </svg>
                <span class="truncate">Inbox</span>
                <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] dark:bg-[#60A5FA] shrink-0"></span>
            </a>

            {{-- 3. Copy Button --}}
            <button
                type="button"
                @click="copyMailboxAddress('{{ $email }}'); copied = true; setTimeout(() => copied = false, 2000)"
                class="h-[40px] w-full flex items-center justify-center gap-2 px-3 rounded-xl font-medium text-[13px] transition-all cursor-pointer hover:bg-[var(--theme-item-hover)] active:scale-[0.98] shadow-xs"
                style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border); color: var(--theme-text-primary);"
            >
                <svg x-show="!copied" width="15" height="15" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" style="color: var(--theme-text-secondary);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <svg x-show="copied" x-cloak style="display: none;" width="15" height="15" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="truncate" x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
            </button>

            {{-- 4. Refresh Button --}}
            <button
                type="button"
                @click="refreshing = true; $wire.dispatch('fetchMessages', { force: true }); setTimeout(() => { refreshing = false }, 1800)"
                class="h-[40px] w-full flex items-center justify-center gap-2 px-3 rounded-xl font-medium text-[13px] transition-all cursor-pointer hover:bg-[var(--theme-item-hover)] active:scale-[0.98] shadow-xs"
                style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border); color: var(--theme-text-primary);"
            >
                <svg
                    width="15" height="15"
                    :class="{ 'animate-spin !text-blue-600 dark:!text-blue-400': refreshing }"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 shrink-0 transition-all"
                    style="color: var(--theme-text-secondary);"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="truncate" x-text="refreshing ? 'Syncing...' : 'Refresh'">Refresh</span>
            </button>

            {{-- 5. Delete Button --}}
            <button
                type="button"
                @click="showDeleteConfirm = true"
                class="h-[40px] col-span-2 w-full flex items-center justify-center gap-2 px-3 rounded-xl font-medium text-[13px] transition-all cursor-pointer hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-400 active:scale-[0.98] shadow-xs"
                style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border); color: var(--theme-text-primary);"
            >
                <svg width="15" height="15" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="truncate text-red-500 font-medium">Delete Mailbox</span>
            </button>

        </div>

    </div>

            {{-- ==================== DESKTOP VERTICAL SIDEBAR (md+) ==================== --}}
    <div class="tmail-desktop-only flex-col h-full select-none w-full">
        
        {{-- Actions & Navigation Container --}}
        <div class="flex flex-col gap-2 mt-1">
            
            {{-- 1. Current Mailbox Account Card (Above + New Mailbox) --}}
            <div class="relative mb-3">
                <button 
                    type="button"
                    @click="openAccount = !openAccount" 
                    class="w-full h-[46px] flex items-center justify-between px-3 rounded-xl transition-all text-left cursor-pointer group shadow-xs hover:brightness-95"
                    style="background-color: var(--theme-account-bg); border: 1px solid var(--theme-account-border); color: var(--theme-text-primary);"
                >
                    <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                        <div 
                            class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-[13px] shrink-0 shadow-xs"
                            style="background-color: var(--theme-account-avatar-bg); color: var(--theme-account-avatar-text);"
                        >
                            {{ strtoupper(substr($email ?: 'A', 0, 1)) }}
                        </div>
                        <span class="text-[13.5px] font-semibold truncate leading-none min-w-0 flex-1 tracking-tight" style="color: var(--theme-text-primary);" title="{{ $email }}">
                            {{ $email ?: __("Generating...") }}
                        </span>
                    </div>
                    <div class="shrink-0 transition-transform duration-150" :class="{ 'rotate-180': openAccount }" style="color: var(--theme-text-secondary);">
                        <svg width="15" height="15" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                {{-- Account Selector Dropdown (Opens downwards) --}}
                <div 
                    x-show="openAccount" 
                    @click.away="openAccount = false" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute top-full left-0 w-full mt-1.5 rounded-xl shadow-2xl overflow-hidden z-50 p-1.5"
                    style="display: none; background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border);"
                >
                    <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wider border-b flex items-center justify-between" style="color: var(--theme-text-muted); border-color: var(--theme-surface-border);">
                        <span>Switch Mailbox</span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 font-mono">{{ count($emails) }}/3 active</span>
                    </div>
                    <div class="max-h-48 overflow-y-auto py-1">
                        @forelse ($emails as $em)
                            @php $remMins = \App\Services\TMail::getEmailRemainingMinutes($em); @endphp
                            <a 
                                href="{{ route('switch', $em) }}" 
                                class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-[13px] font-medium transition-colors"
                                @if ($em === $email)
                                    style="background-color: var(--theme-inbox-bg); color: var(--theme-inbox-text);"
                                @else
                                    style="color: var(--theme-text-primary);"
                                    onmouseover="this.style.backgroundColor='var(--theme-item-hover)'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                @endif
                            >
                                <span class="truncate min-w-0 flex-1">{{ $em }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-mono shrink-0 opacity-80" style="background-color: var(--theme-surface-border); color: var(--theme-text-secondary);" title="Valid for {{ $remMins }} more minutes">
                                    {{ $remMins }}m
                                </span>
                            </a>
                        @empty
                            <div class="px-3 py-2 text-xs text-center" style="color: var(--theme-text-muted);">No other accounts</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 2. New Action Button --}}
            <button 
                type="button"
                @click="showModal = true" 
                class="h-[46px] w-full flex items-center justify-center gap-2 px-4 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.99] text-white font-semibold text-[14.5px] transition-all cursor-pointer shadow-[0_2px_10px_rgba(37,99,235,0.28)] mb-3"
            >
                <svg width="17" height="17" style="width: 17px !important; height: 17px !important; min-width: 17px !important; max-width: 17px !important;" xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>New Mailbox</span>
            </button>

            {{-- 3. Inbox (Active) --}}
            <a 
                href="{{ Util::localizeRoute("mailbox") }}" 
                class="h-[44px] flex items-center justify-between px-4 rounded-xl font-semibold text-[14px] transition-all"
                style="background-color: var(--theme-inbox-bg); color: var(--theme-inbox-text);"
            >
                <div class="flex items-center gap-3.5 min-w-0">
                    <svg width="18" height="18" style="width: 18px !important; height: 18px !important; min-width: 18px !important; max-width: 18px !important;" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                        <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                    </svg>
                    <span class="truncate">Inbox</span>
                </div>
                <span class="w-2 h-2 rounded-full bg-[#2563EB] dark:bg-[#60A5FA] shrink-0"></span>
            </a>

            {{-- 4. Copy --}}
            <button 
                type="button"
                @click="copyMailboxAddress('{{ $email }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                class="h-[44px] w-full flex items-center justify-between px-4 rounded-xl font-medium text-[14px] transition-colors text-left cursor-pointer group hover:bg-[var(--theme-item-hover)]"
                style="color: var(--theme-text-primary);"
            >
                <div class="flex items-center gap-3.5 min-w-0">
                    <svg width="18" height="18" style="width: 18px !important; height: 18px !important; min-width: 18px !important; max-width: 18px !important; color: var(--theme-text-secondary);" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span class="truncate" x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                </div>
                <span x-show="copied" x-cloak class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md shrink-0">Done</span>
            </button>

            {{-- 5. Refresh with Real-Time Animation --}}
            <button 
                type="button"
                @click="refreshing = true; $wire.dispatch('fetchMessages', { force: true }); setTimeout(() => { refreshing = false }, 1800)" 
                class="h-[44px] w-full flex items-center justify-between px-4 rounded-xl font-medium text-[14px] transition-colors text-left cursor-pointer group hover:bg-[var(--theme-item-hover)]"
                style="color: var(--theme-text-primary);"
            >
                <div class="flex items-center gap-3.5 min-w-0">
                    <svg 
                        width="18" height="18" 
                        style="width: 18px !important; height: 18px !important; min-width: 18px !important; max-width: 18px !important; color: var(--theme-text-secondary);" 
                        :class="{ 'animate-spin !text-blue-600 dark:!text-blue-400': refreshing }" 
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-[18px] w-[18px] shrink-0 transition-all" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor" 
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="truncate" x-text="refreshing ? 'Refreshing...' : 'Refresh'">Refresh</span>
                </div>
                <span x-show="refreshing" x-cloak class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-md shrink-0 animate-pulse">Syncing</span>
            </button>

            {{-- 6. Delete --}}
            <button 
                type="button"
                @click="showDeleteConfirm = true" 
                class="h-[44px] w-full flex items-center gap-3.5 px-4 rounded-xl font-medium text-[14px] transition-colors text-left cursor-pointer group hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-400"
                style="color: var(--theme-text-primary);"
            >
                <svg width="18" height="18" style="width: 18px !important; height: 18px !important; min-width: 18px !important; max-width: 18px !important; color: var(--theme-text-secondary);" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] shrink-0 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="truncate">Delete</span>
            </button>

        </div>
    </div>

    {{-- Create New Mailbox Modal --}}
    <div 
        x-show="showModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" 
        style="display: none;"
    >
        <div 
            x-show="showModal" 
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm" 
            @click="showModal = false"
        ></div>
        
        <div 
            x-show="showModal" 
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative rounded-[24px] shadow-2xl w-full max-w-[580px] overflow-hidden z-10"
            style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border);"
        >
            <div class="p-7 sm:p-8">
                
                {{-- Modal Header --}}
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-3.5">
                        <div 
                            class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                            style="background-color: var(--theme-inbox-bg); color: var(--theme-inbox-text);"
                        >
                            <svg width="20" height="20" style="width: 20px !important; height: 20px !important;" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                                <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[20px] font-bold tracking-tight" style="color: var(--theme-text-primary);">Create New Mailbox</h2>
                            <p class="text-[13px] mt-0.5" style="color: var(--theme-text-secondary);">Generate a new temporary email address</p>
                        </div>
                    </div>
                    <button 
                        type="button"
                        @click="showModal = false" 
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-[var(--theme-item-hover)]"
                        style="color: var(--theme-text-secondary);"
                    >
                        <svg width="16" height="16" style="width: 16px !important; height: 16px !important;" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form wire:submit.prevent="create" method="post" @submit="setTimeout(() => { showModal = false }, 800)">
                    @if (config("app.settings.captcha") == "hcaptcha" || config("app.settings.captcha") == "recaptcha2")
                        <div class="mb-4"><x-captcha field="captcha" /></div>
                    @endif

                    <div class="space-y-4">
                        
                        {{-- Username --}}
                        <div>
                            <label for="modal-user" class="block text-[13px] font-medium mb-1.5" style="color: var(--theme-text-primary);">Username</label>
                            <div class="relative flex items-center">
                                <input 
                                    type="text" 
                                    id="modal-user"
                                    name="user"
                                    wire:model.defer="user" 
                                    class="w-full h-[46px] pl-3.5 pr-11 rounded-xl outline-none transition-all text-[14px]" 
                                    style="background-color: var(--theme-input-bg); border: 1px solid var(--theme-input-border); color: var(--theme-input-text);"
                                    placeholder="Enter a username (e.g. alex99, random)" 
                                />
                                <button 
                                    type="button" 
                                    wire:click="random" 
                                    class="absolute right-2 w-7 h-7 rounded-lg flex items-center justify-center transition-colors hover:bg-[var(--theme-inbox-bg)]" 
                                    style="color: var(--theme-text-secondary);"
                                    title="Generate Random Username"
                                >
                                    <svg width="16" height="16" style="width: 16px !important; height: 16px !important;" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                            </div>
                            @error('user') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Domain --}}
                        <div>
                            <label for="modal-domain" class="block text-[13px] font-medium mb-1.5" style="color: var(--theme-text-primary);">Domain</label>
                            <div class="relative flex items-center">
                                <select 
                                    id="modal-domain"
                                    name="domain"
                                    wire:model="domain" 
                                    class="w-full h-[46px] px-3.5 rounded-xl outline-none transition-all appearance-none cursor-pointer text-[14px]"
                                    style="background-color: var(--theme-input-bg); border: 1px solid var(--theme-input-border); color: var(--theme-input-text);"
                                >
                                    <option value="">Select a domain</option>
                                    @foreach ($domains as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3.5 pointer-events-none" style="color: var(--theme-text-secondary);">
                                    <svg width="16" height="16" style="width: 16px !important; height: 16px !important;" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @error('domain') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Notice --}}
                        @if (count($emails) >= 3)
                        <div class="rounded-xl p-3.5 flex items-start gap-2.5 bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400">
                            <svg width="18" height="18" class="shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-[12px] leading-relaxed">
                                <strong class="font-semibold">Maximum limit reached (3/3).</strong> You can keep up to 3 mailboxes at a time. Please delete an existing mailbox before creating another.
                            </p>
                        </div>
                        @else
                        <div class="rounded-xl p-3.5 flex items-start gap-2.5" style="background-color: var(--theme-inbox-bg); color: var(--theme-inbox-text);">
                            <svg width="16" height="16" style="width: 16px !important; height: 16px !important;" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-[12px] leading-relaxed">
                                You can keep up to <strong>3 mailboxes</strong> at a time. Each mailbox is valid for <strong>60 minutes</strong> from issuance, after which it and all its emails are destroyed automatically.
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="mt-6 flex justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="showModal = false" 
                            class="h-[42px] px-5 rounded-xl font-medium text-[14px] transition-colors"
                            style="border: 1px solid var(--theme-surface-border); color: var(--theme-text-primary); background-color: transparent;"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            id="create" 
                            wire:loading.attr="disabled"
                            @if (count($emails) >= 3) disabled style="opacity: 0.5; cursor: not-allowed;" @endif
                            @if (count($emails) >= 3) disabled style="opacity: 0.5; cursor: not-allowed;" @endif
                            class="h-[42px] px-6 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-medium text-[14px] flex items-center gap-2 shadow-sm transition-colors cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="create" class="flex items-center gap-2">
                                <svg width="16" height="16" style="width: 16px !important; height: 16px !important;" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Create Mailbox</span>
                            </span>
                            <span wire:loading wire:target="create" class="flex items-center gap-2">
                                <svg width="16" height="16" style="width: 16px !important; height: 16px !important;" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Creating...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Dialog --}}
    <div 
        x-show="showDeleteConfirm" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" 
        style="display: none;"
    >
        <div 
            x-show="showDeleteConfirm" 
            x-transition.opacity 
            class="fixed inset-0 bg-black/40 backdrop-blur-sm" 
            @click="showDeleteConfirm = false"
        ></div>
        
        <div 
            x-show="showDeleteConfirm" 
            x-transition.scale.95 
            class="relative rounded-[24px] shadow-2xl w-full max-w-[420px] p-6 z-10 text-center"
            style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border);"
        >
            <div class="w-11 h-11 rounded-full bg-red-500/15 text-red-500 flex items-center justify-center mx-auto mb-3.5">
                <svg width="20" height="20" style="width: 20px !important; height: 20px !important;" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="text-[18px] font-bold mb-1.5" style="color: var(--theme-text-primary);">Delete this mailbox?</h3>
            <p class="text-[13px] mb-5 leading-relaxed" style="color: var(--theme-text-secondary);">
                All emails received at this temporary address will be permanently removed.
            </p>
            <div class="flex justify-center gap-2.5">
                <button 
                    type="button" 
                    @click="showDeleteConfirm = false" 
                    class="h-[40px] px-5 rounded-xl font-medium text-[13px] transition-colors"
                    style="border: 1px solid var(--theme-surface-border); color: var(--theme-text-primary); background-color: transparent;"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    wire:click="deleteEmail" 
                    @click="showDeleteConfirm = false" 
                    class="h-[40px] px-5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-medium text-[13px] transition-colors shadow-sm"
                >
                    Delete Mailbox
                </button>
            </div>
        </div>
    </div>

    @if (config("app.settings.captcha") == "recaptcha3")
        <script src="https://www.google.com/recaptcha/api.js?render={{ config("app.settings.recaptcha3.site_key") }}"></script>
        <script>
            const handle = (e) => {
                e.preventDefault();
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{ config("app.settings.recaptcha3.site_key") }}', { action: 'submit' }).then(function (token) {
                        Livewire.dispatch('checkReCaptcha3', {
                            token,
                            action: e.target.id,
                        });
                    });
                });
            };
            document.getElementById('create')?.addEventListener('click', handle);
        </script>
    @endif

    <script>
        function copyMailboxAddress(text) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                document.dispatchEvent(new CustomEvent('showAlert', {
                    bubbles: true,
                    detail: { type: 'success', message: 'Email address copied to clipboard' }
                }));
            }).catch(() => {
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                document.dispatchEvent(new CustomEvent('showAlert', {
                    bubbles: true,
                    detail: { type: 'success', message: 'Email address copied to clipboard' }
                }));
            });
        }
    </script>
</div>
