<div class="flex-1 flex flex-col h-full min-h-0" x-data="{ id: 0 }">
    
    {{-- Error Banner (if any) --}}
    @if ($error)
        <div id="imap-error" class="mb-4 bg-red-50/80 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 rounded-2xl p-4 flex items-start gap-3.5 shrink-0 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-900/50 flex items-center justify-center text-red-600 dark:text-red-400 shrink-0 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md bg-red-600 dark:bg-red-500 text-white text-[11px] font-bold uppercase tracking-wide">Error</span>
                    <h3 class="text-red-900 dark:text-red-300 font-semibold text-[14px]">IMAP Connection Issue</h3>
                </div>
                <p class="text-red-700 dark:text-red-400/80 text-[13px] mt-1">{{ $error }}</p>
            </div>
        </div>
    @endif

    {{-- Unified Master Box Container (Consistent rounded shell from 0 to N emails) --}}
    <div
        class="flex-1 w-full h-full rounded-[24px] shadow-sm flex flex-col overflow-hidden transition-colors"
        style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border); width: 100% !important;"
    >
        @if (!$messages || count($messages) === 0)
            @if (!$initial)
                {{-- Initial Checking State --}}
                <div
                    wire:key="inbox-initial-loading"
                    class="flex-1 w-full h-full flex flex-col items-center justify-center p-8 md:p-14 text-center transition-colors overflow-hidden"
                >
                    <div class="w-10 h-10 rounded-full animate-spin mb-4" style="border: 3px solid #2563EB; border-top-color: transparent;"></div>
                    <h3 class="text-[18px] font-bold tracking-tight mb-1.5" style="color: var(--theme-text-primary);">
                        Checking mailbox...
                    </h3>
                    <p class="text-[14px] font-normal leading-relaxed" style="color: var(--theme-text-secondary);">
                        Connecting to mail server to check for incoming messages
                    </p>
                </div>
            @else
                {{-- Empty Inbox State --}}
                <div
                    wire:key="inbox-empty-view"
                class="flex-1 w-full h-full flex flex-col items-center justify-center p-8 md:p-14 text-center transition-colors overflow-hidden"
            >
                <div class="max-w-[560px] mx-auto flex flex-col items-center justify-center">
                    
                    {{-- Clean Minimal SVG Illustration (Adapts via CSS variables) --}}
                    <div class="mx-auto w-52 h-40 md:w-60 md:h-44 mb-6 flex items-center justify-center">
                        <svg viewBox="0 0 240 180" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-sm">
                            {{-- Background Glow Circles --}}
                            <circle cx="120" cy="95" r="70" style="fill: var(--theme-illustr-circle-1);" />
                            <circle cx="170" cy="70" r="35" style="fill: var(--theme-illustr-circle-2);" />
                            <circle cx="75" cy="110" r="30" style="fill: var(--theme-illustr-circle-2);" />
                            
                            {{-- Paper Airplane Trail --}}
                            <path d="M150 75C170 65 195 45 205 30" stroke="#3B82F6" stroke-opacity="0.5" stroke-width="2.5" stroke-dasharray="4 4" stroke-linecap="round"/>
                            <path d="M130 95C150 90 170 85 180 70" stroke="#3B82F6" stroke-opacity="0.4" stroke-width="2" stroke-dasharray="3 3" stroke-linecap="round"/>
                            
                            {{-- Paper Airplane --}}
                            <g transform="translate(195, 20) rotate(15) scale(0.7)">
                                <path d="M0 0L45 20L0 40L10 20L0 0Z" fill="#3B82F6"/>
                                <path d="M10 20L45 20" stroke="#1D4ED8" stroke-width="2"/>
                            </g>

                            {{-- Main Envelope Back Body --}}
                            <rect x="50" y="65" width="140" height="90" rx="14" style="fill: var(--theme-illustr-env-back);" />
                            
                            {{-- Letter Paper Sliding Out --}}
                            <rect x="62" y="42" width="116" height="75" rx="8" style="fill: var(--theme-illustr-letter); stroke: var(--theme-illustr-letter-border);" stroke-width="1.5"/>
                            <line x1="78" y1="58" x2="120" y2="58" style="stroke: var(--theme-illustr-line-1);" stroke-width="3" stroke-linecap="round"/>
                            <line x1="78" y1="70" x2="162" y2="70" style="stroke: var(--theme-illustr-line-2);" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="78" y1="80" x2="148" y2="80" style="stroke: var(--theme-illustr-line-2);" stroke-width="2.5" stroke-linecap="round"/>

                            {{-- Envelope Front Pocket / Flaps --}}
                            <path d="M50 155C50 155 105 105 120 105C135 105 190 155 190 155" style="fill: var(--theme-illustr-env-front);" />
                            <path d="M50 68L112 112C116.8 115.4 123.2 115.4 128 112L190 68" style="stroke: var(--theme-illustr-env-border);" stroke-width="3" stroke-linejoin="round"/>
                            <path d="M50 155L102 108" style="stroke: var(--theme-illustr-env-border);" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M190 155L138 108" style="stroke: var(--theme-illustr-env-border);" stroke-width="2.5" stroke-linecap="round"/>
                            <rect x="50" y="65" width="140" height="90" rx="14" style="stroke: var(--theme-illustr-env-border);" stroke-width="2.5"/>

                            {{-- Decorative Subtle Dots --}}
                            <circle cx="45" cy="50" r="3.5" fill="#3B82F6" opacity="0.4"/>
                            <circle cx="195" cy="115" r="3" fill="#60A5FA" opacity="0.5"/>
                            <circle cx="65" cy="165" r="2.5" fill="#3B82F6" opacity="0.4"/>
                        </svg>
                    </div>

                    {{-- Heading --}}
                    <h2 class="text-[30px] md:text-[32px] font-bold tracking-tight mb-3" style="color: var(--theme-text-primary);">
                        Your inbox is empty
                    </h2>

                    {{-- Description --}}
                    <p class="text-[15px] md:text-[16px] leading-relaxed max-w-[460px] mx-auto mb-2 font-normal" style="color: var(--theme-text-secondary);">
                        No emails yet. Use this temporary mailbox to receive emails instantly and securely.
                    </p>

                </div>
            </div>
            @endif
        @else
            {{-- Incoming Messages Full-Width List (Spans 100% full width from pure left to pure right) --}}
            <div
                wire:key="inbox-messages-list-view"
                class="w-full h-full flex flex-col overflow-y-auto transition-colors divide-y divide-[var(--theme-surface-border)]"
                x-show="id === 0"
                style="width: 100% !important;"
            >
                @foreach ($messages as $message)
                    <div
                        wire:key="msg-item-{{ $message['id'] }}"
                        @click="id = {{ $message['id'] }}"
                        class="w-full group flex items-center justify-between px-6 py-4 md:px-8 md:py-4.5 cursor-pointer transition-all hover:bg-[var(--theme-item-hover)] select-none text-left"
                        style="width: 100% !important; border-bottom: 1px solid var(--theme-surface-border);"
                    >
                        <div class="flex items-center gap-4 min-w-0 flex-1 pr-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-[15px] shrink-0 shadow-sm" style="background-color: var(--theme-account-avatar-bg); color: var(--theme-account-avatar-text);">
                                {{ strtoupper(substr($message['sender_name'] ?? 'M', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1 text-left">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-[15px] truncate" style="color: var(--theme-text-primary);">
                                        {{ $message['sender_name'] }}
                                    </span>
                                    @if(!empty($message['sender_email']))
                                        <span class="text-[13px] truncate font-normal" style="color: var(--theme-text-muted);">
                                            <{{ $message['sender_email'] }}>
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[14px] font-medium truncate" style="color: var(--theme-text-secondary);">
                                    {{ $message['subject'] }}
                                </div>
                            </div>
                        </div>
                        <div
                            x-data="{
                                timeDiff: '{{ $message['datediff'] }}',
                                updateTime() {
                                    if (typeof window.formatTimeAgo === 'function') {
                                        const updated = window.formatTimeAgo('{{ !empty($message['timestamp']) ? (is_string($message['timestamp']) ? $message['timestamp'] : (method_exists($message['timestamp'], 'format') ? $message['timestamp']->format('c') : '')) : '' }}');
                                        if (updated) this.timeDiff = updated;
                                    }
                                }
                            }"
                            x-init="
                                updateTime();
                                let timer = setInterval(() => updateTime(), 10000);
                                $cleanup(() => clearInterval(timer));
                            "
                            class="text-[12px] whitespace-nowrap shrink-0 font-medium ml-4 text-right"
                            style="color: var(--theme-text-muted);"
                            x-text="timeDiff"
                        >
                            {{ $message['datediff'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Full-Width Single Message Reading Pane (Visible when id !== 0) --}}
            <div
                wire:key="inbox-messages-reader-view"
                class="flex-1 w-full h-full flex flex-col min-w-0 transition-colors overflow-hidden"
                x-show="id !== 0"
                style="display: none; width: 100% !important;"
            >
                @if ($messages && count($messages) > 0)
                    @foreach ($messages as $message)
                        <div wire:key="msg-reading-{{ $message['id'] }}" x-show="id === {{ $message['id'] }}" class="flex-1 flex flex-col h-full min-h-0" style="display: none;">
                            
                            {{-- Email Header with Bold Back Button --}}
                            <div class="p-4 md:p-6 shrink-0" style="border-bottom: 1px solid var(--theme-surface-border); background-color: var(--theme-surface-header);">
                                <div class="flex items-center justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                        {{-- Bold Arrow Button to go back to Messages List --}}
                                        <button 
                                            type="button"
                                            @click="id = 0" 
                                            class="w-10 h-10 rounded-xl flex items-center justify-center transition-all shadow-sm shrink-0 cursor-pointer hover:opacity-80 active:scale-95"
                                            style="background-color: var(--theme-surface); color: var(--theme-text-primary); border: 1px solid var(--theme-surface-border);"
                                            title="Back to messages"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                            </svg>
                                        </button>
                                        
                                        <h2 class="text-[18px] md:text-[20px] font-bold leading-tight truncate" style="color: var(--theme-text-primary);" title="{{ $message['subject'] }}">
                                            {{ $message['subject'] }}
                                        </h2>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <a 
                                            href="#" 
                                            class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors hover:opacity-80 shadow-sm"
                                            style="color: var(--theme-text-secondary); background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border);"
                                            title="Download EML"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </a>
                                        <button 
                                            type="button" 
                                            wire:click="delete({{ $message['id'] }})" 
                                            @click="id = 0"
                                            class="w-9 h-9 rounded-xl flex items-center justify-center text-red-500 hover:bg-red-50/20 transition-colors shadow-sm"
                                            style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border);"
                                            title="Delete Message"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3.5 pl-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-[15px] shrink-0 shadow-sm" style="background-color: var(--theme-account-avatar-bg); color: var(--theme-account-avatar-text);">
                                        {{ strtoupper(substr($message['sender_name'], 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-[14px] truncate" style="color: var(--theme-text-primary);">{{ $message['sender_name'] }}</span>
                                            <span class="text-[13px] truncate" style="color: var(--theme-text-muted);"><{{ $message['sender_email'] }}></span>
                                        </div>
                                        <div class="text-[12px] mt-0.5" style="color: var(--theme-text-muted);">
                                            {{ $message['date'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Email Body (Iframe) --}}
                            <div class="flex-1 min-h-0" style="background-color: var(--theme-surface);">
                                <iframe
                                    class="w-full h-full border-0"
                                    srcdoc="{{ $message['content'] }}"
                                    sandbox="allow-same-origin allow-popups"
                                    referrerpolicy="no-referrer"
                                ></iframe>
                            </div>

                            {{-- Attachments --}}
                            @if (isset($message['attachments']) && count($message['attachments']) > 0)
                                <div class="p-4 md:p-5 shrink-0" style="border-top: 1px solid var(--theme-surface-border); background-color: var(--theme-surface-header);">
                                    <div class="text-[12px] font-bold uppercase tracking-wider mb-3" style="color: var(--theme-text-muted);">
                                        Attachments ({{ count($message['attachments']) }})
                                    </div>
                                    <div class="flex flex-wrap gap-2.5">
                                        @foreach ($message['attachments'] as $attachment)
                                            <a 
                                                href="{{ $attachment['url'] }}" 
                                                download 
                                                class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl transition-all group"
                                                style="background-color: var(--theme-surface); border: 1px solid var(--theme-surface-border);"
                                            >
                                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background-color: var(--theme-account-avatar-bg); color: var(--theme-account-avatar-text);">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                </div>
                                                <span class="text-[13px] font-medium truncate max-w-[150px]" style="color: var(--theme-text-primary);">
                                                    Attachment
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif
</div>
