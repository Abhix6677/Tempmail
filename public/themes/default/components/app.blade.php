<main class="flex-1 lg:flex">
    <div x-data="{ id: 0 }" class="flex-1 lg:flex">
    @if ($error)
        <div id="imap-error" class="flex items-center w-full h-full fixed top-0 left-0 bg-red-900 dark:bg-red-800 opacity-75 z-50">
            <div class="flex flex-col mx-auto">
                <div class="w-36 mx-auto text-white dark:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-3xl text-center text-white dark:text-gray-200 my-5">{{ __("IMAP Broken") }}</div>
                <div class="p-2 mx-auto bg-red-800 dark:bg-red-700 text-white dark:text-gray-200 leading-none lg:rounded-full flex lg:inline-flex" role="alert">
                    <span class="flex rounded-full bg-red-500 dark:bg-red-400 uppercase px-2 py-1 text-xs font-bold mr-3">{{ __("Error") }}</span>
                    <span class="font-semibold mr-2 text-left flex-auto">{{ $error }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="w-full lg:w-1/3 bg-white dark:bg-gray-900 flex flex-col">
        @if ($messages)
            <div class="messages flex flex-col divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($messages as $i => $message)
                    @if ($i / 3 == 0 && config("app.settings.ads.four"))
                        <div class="max-w-full adz-four">{!! config("app.settings.ads.four") !!}</div>
                    @endif

                    @if (! in_array($i, $deleted))
                        <div x-on:click="
                            id = {{ $message["id"] }}
                            document
                                .querySelector('.message-content')
                                .scrollIntoView({ behavior: 'smooth' })
                        " class="w-full p-5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800" data-id="{{ $message["id"] }}">
                            <div class="flex justify-between">
                                <div>
                                    <div class="text-gray-800 dark:text-gray-200">{{ $message["sender_name"] }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">{{ $message["sender_email"] }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-800 dark:text-gray-200 text-sm">{{ $message["datediff"] }}</div>
                                </div>
                            </div>
                            <div class="text-gray-600 dark:text-gray-400 mt-5 text-sm truncate">{{ $message["subject"] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="grow flex justify-center items-center h-40 text-gray-400 dark:text-gray-500 text-2xl">
                {{ $initial ? __("Empty Inbox") : __("Fetching") . "..." }}
            </div>
        @endif
    </div>
    <div class="message-content w-full lg:w-2/3 bg-white dark:bg-gray-900 border-1 border-l border-gray-200 dark:border-gray-800 flex flex-col">
        <div x-show="id === 0" class="flex-1 hidden lg:flex">
            <div class="w-2/3 m-auto">
                <img class="m-auto max-w-full" src="{{ asset("images/inbox.png") }}" alt="mails" />
            </div>
        </div>
        @foreach ($messages as $message)
            <div x-show="id === {{ $message["id"] }}" id="message-{{ $message["id"] }}" class="flex-1 lg:flex flex-col">
                <textarea class="hidden">To: {{ $this->email }}&#13;From: "{{ $message["sender_name"] }}" <{{ $message["sender_email"] }}>&#13;Subject: {{ $message["subject"] }}&#13;Date: {{ $message["date"] }}&#13;Content-Type: text/html&#13;&#13;{{ $message["content"] }}</textarea>
                <div class="flex flex-col flex-none py-5 px-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-gray-900 dark:text-gray-200 text-lg">{{ $message["subject"] }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ $message["sender_name"] }} - {{ $message["sender_email"] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ $message["date"] }}</div>
                        </div>
                    </div>
                    <div class="flex mt-5">
                        <a class="download text-xs font-semibold bg-blue-700 dark:bg-blue-600 py-1 px-3 rounded-md text-white dark:text-gray-200" href="#" data-id="{{ $message["id"] }}">{{ __("Download") }}</a>
                        <span class="mr-2"></span>
                        <button x-on:click="
                            id = 0
                            document.querySelector(`[data-id='{{ $message["id"] }}']`).remove()
                        " wire:click="delete({{ $message["id"] }})" class="text-xs font-semibold bg-red-700 dark:bg-red-600 py-1 px-3 rounded-md text-white dark:text-gray-200">{{ __("Delete") }}</button>
                    </div>
                </div>
                <div class="w-full flex flex-grow px-5 pb-6">
                    <div class="w-full bg-white text-gray-900 rounded-lg shadow-sm overflow-auto">
                        <iframe class="w-full" style="min-height: 500px;" srcdoc="{{ $message["content"] }}" frameborder="0"></iframe>
                    </div>
                </div>
                @if (count($message["attachments"]) > 0)
                    <span class="pt-5 pb-3 px-6 text-xs dark:text-gray-400">{{ __("Attachments") }}</span>
                    <div class="flex pb-5 px-6">
                        @foreach ($message["attachments"] as $attachment)
                            <a class="py-2 px-3 mr-3 text-sm border-2 border-black dark:border-gray-800 rounded-md hover:bg-black dark:hover:bg-gray-800 hover:text-white dark:hover:text-gray-200" href="{{ $attachment["url"] }}" download>
                                <i class="fas fa-chevron-circle-down"></i>
                                <span class="ml-2">{{ $attachment["file"] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    </div>
</main>
