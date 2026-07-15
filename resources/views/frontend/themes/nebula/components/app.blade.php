<main class="flex-1">
    <div x-data="{ show: false, id: 0 }" class="flex-1 text-sm lg:rounded-lg">
    @if ($error)
        <div id="imap-error" class="flex items-center w-full h-full fixed top-0 left-0 bg-red-900 dark:bg-red-800 opacity-75 dark:opacity-80 z-50">
            <div class="flex flex-col mx-auto">
                <div class="w-36 mx-auto text-white dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-3xl text-center text-white dark:text-gray-100 my-5">{{ __("IMAP Broken") }}</div>
                <div class="p-2 mx-auto bg-red-800 dark:bg-red-700 text-white dark:text-gray-100 leading-none lg:lg:rounded-full flex lg:inline-flex" role="alert">
                    <span class="flex lg:rounded-full bg-red-500 dark:bg-red-600 uppercase px-2 py-1 text-xs font-bold mr-3">{{ __("Error") }}</span>
                    <span class="font-semibold mr-2 text-left flex-auto">{{ $error }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="text-sm lg:rounded-lg">
        @if ($messages)
            <div class="mailbox">
                <div x-show="!show" class="list">
                    <div class="head flex items-center gap-3 pt-5 pb-6 px-7 lg:rounded-t-md text-gray-900 dark:text-gray-100" style="background-color: {{ config("app.settings.colors.primary") }}20">
                        <div class="w-1/2 md:w-3/12">{{ __("Sender") }}</div>
                        <div class="w-1/2 md:w-7/12">{{ __("Subject") }}</div>
                        <div class="hidden md:flex md:w-2/12 justify-end">{{ __("Time") }}</div>
                    </div>
                    <div class="messages flex flex-col justify-start">
                        @foreach ($messages as $i => $message)
                            @if ($i / 3 == 0 && config("app.settings.ads.four"))
                                <div class="max-w-full adz-four">{!! config("app.settings.ads.four") !!}</div>
                            @endif

                            @if (! in_array($i, $deleted))
                                <div x-on:click="
                                    show = true
                                    id = {{ $message["id"] }}
                                    document
                                        .querySelector('button.delete')
                                        .setAttribute('wire:click', 'delete({{ $message["id"] }})')
                                " class="flex items-center gap-3 hover:bg-gray-100 dark:hover:bg-gray-800 border-b border-dashed border-gray-100 dark:border-gray-800 py-4 px-7 cursor-pointer text-gray-900 dark:text-gray-100" data-id="{{ $message["id"] }}">
                                    <div class="w-1/2 md:w-3/12">
                                        {{ $message["sender_name"] }}
                                        <div class="text-xs overflow-ellipsis text-gray-600 dark:text-gray-400">{{ $message["sender_email"] }}</div>
                                    </div>
                                    <div class="w-1/2 md:w-7/12">{{ $message["subject"] }}</div>
                                    <div class="hidden md:block w-full md:w-2/12">
                                        <div class="flex justify-end text-gray-600 dark:text-gray-400">
                                            {{ $message["datediff"] }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div x-show="show" class="message">
                    <div class="head flex items-center text-white dark:text-gray-100 py-5 px-7 lg:rounded-t-md" style="background-color: {{ config("app.settings.colors.primary") }}99">
                        <div class="w-full flex justify-between items-center">
                            <div x-on:click="show = false" class="flex items-center cursor-pointer hover:text-gray-200 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span class="ml-2">{{ __("Go Back to Inbox") }}</span>
                            </div>
                            <div class="flex gap-3">
                                <a class="download border-b border-white dark:border-gray-300 hover:border-gray-200 dark:hover:text-gray-200" href="#" x-bind:data-id="id">{{ __("Download") }}</a>
                                <button x-on:click="
                                    id = 0
                                    show = false
                                    document
                                        .querySelector(`.mailbox .list [data-id='{{ $message["id"] }}']`)
                                        .remove()
                                " class="delete border-b border-white dark:border-gray-300 hover:border-gray-200 dark:hover:text-gray-200" wire:click="delete(1)">{{ __("Delete") }}</button>
                            </div>
                        </div>
                    </div>
                    @foreach ($messages as $message)
                        <div x-show="id === {{ $message["id"] }}" id="message-{{ $message["id"] }}" class="message">
                            <textarea class="hidden">To: {{ $this->email }}&#13;From: "{{ $message["sender_name"] }}" <{{ $message["sender_email"] }}>&#13;Subject: {{ $message["subject"] }}&#13;Date: {{ $message["date"] }}&#13;Content-Type: text/html&#13;&#13;{{ $message["content"] }}</textarea>
                            <div class="flex justify-between items-center py-4 px-7 text-gray-900 dark:text-gray-100">
                                <div>
                                    {{ $message["sender_name"] }}
                                    <div class="text-xs overflow-ellipsis text-gray-600 dark:text-gray-400">
                                        {{ $message["sender_email"] }}
                                    </div>
                                </div>
                                <div>
                                    {{ __("Date") }}
                                    <div class="text-xs overflow-ellipsis text-gray-600 dark:text-gray-400">
                                        {{ $message["date"] }}
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-b border-dashed border-gray-200 dark:border-gray-600 py-4 px-7 text-gray-900 dark:text-gray-100">
                                {{ $message["subject"] }}
                            </div>
                            <div class="text-wrap py-4 px-7 bg-white dark:bg-gray-800">
                                <iframe class="w-full flex flex-grow" srcdoc="{{ $message["content"] }}" frameborder="0"></iframe>
                                @if (count($message["attachments"]) > 0)
                                    <span class="pt-5 pb-3 px-6 text-xs text-gray-600 dark:text-gray-400">{{ __("Attachments") }}</span>
                                    <div class="flex pb-5 px-6">
                                        @foreach ($message["attachments"] as $attachment)
                                            <a class="py-2 px-3 mr-3 text-sm border-2 border-black dark:border-gray-300 lg:rounded-md hover:bg-black dark:hover:bg-gray-700 hover:text-white dark:hover:text-gray-100 text-gray-900 dark:text-gray-100" href="{{ $attachment["url"] }}" download>
                                                <i class="fas fa-chevron-circle-down"></i>
                                                <span class="ml-2">{{ $attachment["file"] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div id="empty-inbox" class="flex items-center">
                <div class="flex-1 flex flex-col justify-center items-center text-gray-500 dark:text-gray-400 h-80">
                    <div class="text-lg">{{ $initial ? __("Empty Inbox") : __("Fetching") . "..." }}</div>
                </div>
            </div>
        @endif
    </div>
</main>
