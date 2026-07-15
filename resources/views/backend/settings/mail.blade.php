<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Mail Setup") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Mail Setup is required to send out any communications for the contact form.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="mail" value="{{ __('Mail Type') }}" />
            <div class="relative">
                <x-select id="mail" class="mt-1 block w-full" wire:model.live="state.mail.type">
                    <option selected disabled value="empty">{{ __("Select a Type") }}</option>
                    <option value="smtp">{{ __("SMTP") }}</option>
                    <option value="log">{{ __("Log") }}</option>
                </x-select>
            </div>
            <x-input-error for="mail" class="mt-2" />
            @if ($state["mail"]["type"] == "log")
                <small class="text-gray-500 block mt-2">{{ __("Your emails will be stored at /storage/logs/laravel.log file") }}</small>
            @endif
        </div>
        @if ($state["mail"]["type"] == "smtp")
            <div class="col-span-6 sm:col-span-4 grid grid-cols-1 gap-6">
                <div>
                    <x-label for="smtp_host" value="{{ __('Host') }}" />
                    <x-input id="smtp_host" type="text" class="mt-1 block w-full" wire:model="state.mail.smtp.host" />
                    <x-input-error for="state.mail.smtp.host" class="mt-1 mb-2" />
                </div>
                <div>
                    <x-label for="smtp_port" value="{{ __('Port') }}" />
                    <x-input id="smtp_port" type="text" class="mt-1 block w-full" wire:model="state.mail.smtp.port" />
                    <x-input-error for="state.mail.smtp.port" class="mt-1 mb-2" />
                </div>
                <div>
                    <x-label for="smtp_username" value="{{ __('Username') }}" />
                    <x-input id="smtp_username" type="text" class="mt-1 block w-full" wire:model="state.mail.smtp.username" />
                    <x-input-error for="state.mail.smtp.username" class="mt-1 mb-2" />
                </div>
                <div x-data="{ show_password: false }">
                    <x-label for="smtp_password" value="{{ __('Password') }}" />
                    <div class="relative">
                        <x-input id="smtp_password" x-bind:type="show_password ? 'text' : 'password'" class="mt-1 block w-full" placeholder="{{ __('Enter the Password') }}" wire:model="state.mail.smtp.password" autocomplete="new-password" />
                        <div x-on:click="show_password = !show_password" x-text="show_password ? 'HIDE' : 'SHOW'" class="cursor-pointer absolute inset-y-0 right-0 flex items-center px-5 text-xs"></div>
                    </div>
                    <x-input-error for="state.mail.smtp.password" class="mt-2" />
                </div>
                <div>
                    <x-label for="smtp_encryption" value="{{ __('Encryption') }}" />
                    <x-select id="smtp_encryption" class="mt-1 block w-full" wire:model="state.mail.smtp.encryption">
                        <option selected disabled value="empty">{{ __("Select a Encryption Type") }}</option>
                        <option value="tls">{{ __("TLS") }}</option>
                        <option value="ssl">{{ __("SSL") }}</option>
                        <option value="none">{{ __("None") }}</option>
                    </x-select>
                    <x-input-error for="state.mail.smtp.encryption" class="mt-2" />
                </div>
                <div>
                    <x-label for="from_address" value="{{ __('From Address') }}" />
                    <x-input id="from_address" type="text" class="mt-1 block w-full" wire:model="state.mail.from.address" />
                    <x-input-error for="state.mail.from.address" class="mt-1 mb-2" />
                </div>
                <div>
                    <x-label for="from_name" value="{{ __('From Name') }}" />
                    <x-input id="from_name" type="text" class="mt-1 block w-full" wire:model="state.mail.from.name" />
                    <x-input-error for="state.mail.from.name" class="mt-1 mb-2" />
                </div>
            </div>
        @endif
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
