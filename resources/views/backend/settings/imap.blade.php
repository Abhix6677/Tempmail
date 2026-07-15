<x-form-section submit="save">
    <x-slot name="title">
        {{ __("IMAP Configuration") }}
    </x-slot>

    <x-slot name="description">
        {{ __("IMAP Settings for internal or external server from which TMail will fetch emails.") }}
    </x-slot>

    <x-slot name="form">
        @if ($state["error"])
            <div class="col-span-6">
                <div class="w-full py-3 px-4 overflow-hidden rounded-md flex items-center border bg-red-50 border-red-500">
                    <div class="text-red-500 w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-lg text-gray-600 font-semibold">{{ __("Error") }}</div>
                        <div class="text-sm">{{ $state["error"] }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-span-6 sm:col-span-4">
            <x-label for="hostname" value="{{ __('Hostname') }}" />
            <x-input id="hostname" type="text" class="mt-1 block w-full" wire:model="state.imap.host" />
            <x-input-error for="state.imap.host" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="port" value="{{ __('Port') }}" />
            <x-input id="port" type="text" class="mt-1 block w-full" wire:model="state.imap.port" />
            <x-input-error for="state.imap.port" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="encryption" value="{{ __('Encryption') }}" />
            <x-select class="mt-1 block w-full" wire:model="state.imap.encryption">
                <option value="notls">{{ __("None") }}</option>
                <option value="ssl">{{ __("SSL") }}</option>
                <option value="tls">{{ __("TLS") }}</option>
            </x-select>
            <x-input-error for="state.imap.encryption" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <label for="validate_cert" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Validate Encryption Certificate") }}</x-label>
                <x-toggle id="validate_cert" wire:model="state.imap.validate_cert" />
            </label>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="username" value="{{ __('Username') }}" />
            <x-input id="username" type="text" class="mt-1 block w-full" wire:model="state.imap.username" />
            <x-input-error for="state.imap.username" class="mt-2" />
        </div>
        <div x-data="{ show: false }" class="col-span-6 sm:col-span-4">
            <x-label for="password" value="{{ __('Password') }}" />
            <div class="relative">
                <x-input id="password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full" wire:model="state.imap.password" />
                <div x-on:click="show = !show" x-text="show ? 'HIDE' : 'SHOW'" class="cursor-pointer absolute inset-y-0 right-0 flex items-center px-5 text-xs"></div>
            </div>
            <x-input-error for="state.imap.password" class="mt-2" />
        </div>
        <div x-data="{ show_advance: false }" class="col-span-6 sm:col-span-4">
            <label for="show_advance" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Show Advance") }}</x-label>
                <x-toggle id="show_advance" x-model="show_advance" />
            </label>
            <div x-show="show_advance" class="mt-6">
                <x-label for="default_account" value="{{ __('Default Account') }}" />
                <x-input id="default_account" type="text" class="mt-1 block w-full" wire:model="state.imap.default_account" />
            </div>
            <div x-show="show_advance" class="mt-6">
                <x-label for="protocol" value="{{ __('Protocol') }}" />
                <x-input id="protocol" type="text" class="mt-1 block w-full" wire:model="state.imap.protocol" />
            </div>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <label for="cc_check" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Check CC Field") }}</x-label>
                <x-toggle id="cc_check" wire:model="state.imap.cc_check" />
            </label>
            <small>{{ __("If enabled TMail will check the CC field as well while fetching mails.") }}</small>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __("Saved.") }}
        </x-action-message>
        <x-button>
            {{ __("Save") }}
        </x-button>
    </x-slot>
</x-form-section>
