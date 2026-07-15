<div x-data="{ in_app: {{ $in_app ? "true" : "false" }} }">
    <div>
        <div x-show.transition.in="in_app" class="app-action mt-4 px-8" style="display: none">
            @if (config("app.settings.captcha") == "hcaptcha" || config("app.settings.captcha") == "recaptcha2")
                <div class="flex items-center justify-center">
                    <x-captcha field="captcha" />
                </div>
            @endif

            <form wire:submit.prevent="create" method="post">
                <div class="max-w-screen-lg mx-auto flex space-x-2 items-center">
                    @if (count($emails) > 0 && $in_app)
                        <a href="{{ Util::localizeRoute("mailbox") }}" class="appearance-none rounded-md py-3 px-5 bg-white dark:bg-gray-800 bg-opacity-25 dark:bg-opacity-40 text-white dark:text-gray-100 text-sm cursor-pointer focus:outline-none"><i class="fas fa-angle-double-left"></i></a>
                    @endif

                    <div class="flex-1 bg-white dark:bg-gray-800 text-white dark:text-gray-100 bg-opacity-20 dark:bg-opacity-40 rounded-md flex items-center">
                        <input class="appearance-none bg-transparent flex-1 border-0 rounded-md py-4 px-5 focus:outline-none placeholder-white dark:placeholder-gray-400 placeholder-opacity-30" type="text" name="user" wire:model.defer="user" placeholder="{{ __("Enter Username") }}" />
                        <div class="border-l-2 h-4 border-gray-200 dark:border-gray-600"></div>
                        <div class="relative">
                            <x-dropdown width="full">
                                <x-slot name="trigger">
                                    <input x-ref="domain" type="text" class="block appearance-none bg-transparent border-0 w-full py-4 px-5 pr-8 cursor-pointer focus:outline-none select-none rounded-md placeholder-white dark:placeholder-gray-400 placeholder-opacity-30" placeholder="{{ __("Select Domain") }}" name="domain" wire:model="domain" readonly />
                                </x-slot>
                                <x-slot name="content">
                                    @foreach ($domains as $domain)
                                        <a x-on:click="
                                            $refs.domain.value = '{{ $domain }}'
                                            $wire.setDomain('{{ $domain }}')
                                        " class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out">{{ $domain }}</a>
                                    @endforeach
                                </x-slot>
                            </x-dropdown>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-gray-200 dark:text-gray-400">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                            </div>
                        </div>
                        <input class="block appearance-none rounded-r-md py-4 px-5 bg-teal-500 dark:bg-teal-600 text-white dark:text-gray-100 cursor-pointer focus:outline-none" style="background-color: {{ config("app.settings.colors.secondary") }}" type="submit" value="{{ __("Create") }}" />
                    </div>
                </div>
            </form>
            <div class="py-2 text-gray-200 dark:text-gray-400 text-center">{{ __("or") }}</div>
            <form wire:submit.prevent="random" class="flex justify-center mb-1" method="post">
                <input class="appearance-none rounded-md py-2 px-5 bg-yellow-500 dark:bg-yellow-600 text-white dark:text-gray-100 cursor-pointer focus:outline-none" style="background-color: {{ config("app.settings.colors.tertiary") }}" type="submit" value="{{ __("Create a Random Email") }}" />
                @if (! $in_app)
                    <button type="button" x-on:click="in_app = false" class="ml-2 appearance-none rounded-md py-2 px-5 bg-white dark:bg-gray-800 bg-opacity-10 dark:bg-opacity-20 text-white dark:text-gray-100 text-sm cursor-pointer focus:outline-none"><i class="fas fa-times"></i></button>
                @endif
            </form>
        </div>
        <div x-show.transition.in="!in_app" class="in-app-actions mt-4 px-8" style="display: none">
            <form class="max-w-screen-lg mx-auto" action="#" method="post">
                <div class="relative">
                    <x-dropdown align="top" width="full">
                        <x-slot name="trigger">
                            <div class="block appearance-none w-full bg-white dark:bg-gray-800 text-white dark:text-gray-100 py-4 px-5 pr-8 bg-opacity-10 dark:bg-opacity-20 rounded-md cursor-pointer focus:outline-none select-none">{{ $email }}</div>
                        </x-slot>
                        <x-slot name="content">
                            @foreach ($emails as $item)
                                <x-dropdown-link href="{{ route('switch', $item) }}" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ $item }}
                                </x-dropdown-link>
                            @endforeach
                        </x-slot>
                    </x-dropdown>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white dark:text-gray-100">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                    </div>
                </div>
            </form>
            <div class="divider mt-5"></div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 max-w-screen-lg mx-auto">
                <div class="btn_copy bg-white dark:bg-gray-800 bg-opacity-10 dark:bg-opacity-20 text-white dark:text-gray-100 rounded-md py-2 text-center hover:bg-opacity-25 dark:hover:bg-opacity-40 cursor-pointer flex justify-center items-center space-x-2">
                    <i class="far fa-copy"></i>
                    <div>{{ __("Copy") }}</div>
                </div>
                <div onclick="this.querySelector('#refresh').classList.remove('pause-spinner')" wire:click="$dispatch('fetchMessages')" class="bg-white dark:bg-gray-800 bg-opacity-10 dark:bg-opacity-20 text-white dark:text-gray-100 rounded-md py-5 text-center hover:bg-opacity-25 dark:hover:bg-opacity-40 cursor-pointer flex justify-center items-center space-x-2">
                    <i id="refresh" class="fas fa-sync-alt fa-spin"></i>
                    <div>{{ __("Refresh") }}</div>
                </div>
                <div x-on:click="in_app = true" class="bg-white dark:bg-gray-800 bg-opacity-10 dark:bg-opacity-20 text-white dark:text-gray-100 rounded-md py-5 text-center hover:bg-opacity-25 dark:hover:bg-opacity-40 cursor-pointer flex justify-center items-center space-x-2">
                    <i class="far fa-plus-square"></i>
                    <div>{{ __("New") }}</div>
                </div>
                <div wire:click="deleteEmail" class="bg-white dark:bg-gray-800 bg-opacity-10 dark:bg-opacity-20 text-white dark:text-gray-100 rounded-md py-5 text-center hover:bg-opacity-25 dark:hover:bg-opacity-40 cursor-pointer flex justify-center items-center space-x-2">
                    <i class="far fa-trash-alt"></i>
                    <div>{{ __("Delete") }}</div>
                </div>
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
            document.getElementById('create').addEventListener('click', handle);
            document.getElementById('random').addEventListener('click', handle);
        </script>
    @endif
</div>
