<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Advance") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You can control here, advance settings like adding Custom CSS or JS, adding HTML code to Header or Footer and configuring API Keys for advance access.") }}
    </x-slot>

    <x-slot name="form">
        <div x-data="{
            lock: {{ ($state["lock"]["enable"] ?? false) ? "true" : "false" }},
            show: false,
        }" class="col-span-6 sm:col-span4">
            <label for="lock_tmail" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Lock TMail") }}</x-label>
                <x-toggle x-model="lock" id="lock_tmail" wire:model="state.lock.enable"></x-toggle>
            </label>
            <div x-show="lock">
                <div class="mt-2">
                    <x-label for="lock_text" value="{{ __('Text') }}" />
                    <x-textarea id="lock_text" class="mt-1 block w-full resize-y" placeholder="Lock screen Text (HTML is allowed)" wire:model="state.lock.text"></x-textarea>
                    <x-input-error for="lock_text" class="mt-2" />
                </div>
                <div class="mt-2">
                    <x-label for="lock_password" value="{{ __('Password') }}" />
                    <div class="relative">
                        <x-input id="lock_password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full" autocomplete="new-password" wire:model="state.lock.password" />
                        <div x-on:click="show = !show" x-text="show ? 'HIDE' : 'SHOW'" class="cursor-pointer absolute inset-y-0 right-0 flex items-center px-5 text-xs"></div>
                    </div>
                    <x-input-error for="state.lock.password" class="mt-2" />
                </div>
            </div>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label value="{{ __('API Keys') }}" />
            @foreach ($state["api_keys"] as $key => $api_key)
                <div class="flex {{ $key > 0 ? "mt-1" : "" }}">
                    <x-input type="text" class="mt-1 block w-full" wire:model="state.api_keys.{{ $key }}" />
                    <button type="button" wire:click="remove({{ $key }})" class="form-input rounded-md ml-2 mt-1 bg-red-700 text-white border-0"><i class="hgi hgi-stroke hgi-delete-02"></i></button>
                </div>
                <x-input-error for="state.api_keys.{{ $key }}" class="mt-1 mb-2" />
            @endforeach

            <x-button class="mt-2" type="button" wire:click="add">{{ __("Add") }}</x-button>
        </div>
        <div class="col-span-6">
            <x-label for="global_css" value="{{ __('Global CSS') }}" />
            <x-textarea id="global_css" class="mt-4 block w-full resize-y" placeholder="Enter your CSS Code here" wire:model="state.global.css"></x-textarea>
            <x-input-error for="global_css" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="global_js" value="{{ __('Global JS') }}" />
            <x-textarea id="global_js" class="mt-4 block w-full resize-y" placeholder="Enter your JS Code here" wire:model="state.global.js"></x-textarea>
            <x-input-error for="global_js" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="global_header" value="{{ __('Global Header') }}" />
            <x-textarea id="global_header" class="mt-4 block w-full resize-y" placeholder="Enter your HTML Code here" wire:model="state.global.header"></x-textarea>
            <x-input-error for="global_header" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="global_footer" value="{{ __('Global Footer') }}" />
            <x-textarea id="global_footer" class="mt-4 block w-full resize-y" placeholder="Enter your HTML Code here" wire:model="state.global.footer"></x-textarea>
            <x-input-error for="global_footer" class="mt-2" />
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
