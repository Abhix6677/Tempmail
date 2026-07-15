<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Theme Options") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You will see here theme specific options.") }}
    </x-slot>

    <x-slot name="form">
        @if (config("app.settings.theme") == "groot" || config("app.settings.theme") == "drax")
            <div class="col-span-6">
                <x-label for="mailbox_page" value="{{ __('Mailbox Page') }}" />
                <div class="relative">
                    <x-select class="mt-1 block w-full" wire:model="state.theme_options.mailbox_page">
                        <option value="0">{{ __("None") }}</option>
                        @foreach ($state["pages"] as $id => $page)
                            <option value="{{ $id }}">{{ $page }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-input-error for="state.theme_options.mailbox_page" class="mt-2" />
                <small class="block mt-1">{{ __("Selected Page Content will be shown on App Page") }}</small>
            </div>
        @endif

        @if (config("app.settings.theme") == "drax")
            <div class="col-span-6">
                <label class="block text-sm text-gray-700 font-bold mb-2">{{ __("Special Button") }}</label>
                <div class="flex">
                    <div>
                        <x-label for="drax_btn_text" value="{{ __('Text') }}" />
                        <x-input type="text" class="mt-1 block w-full" wire:model="state.theme_options.button.text" placeholder="eg. thehp" />
                    </div>
                    <div class="ml-2 flex-1">
                        <x-label for="drax_btn_link" value="{{ __('Link') }}" />
                        <x-input type="text" class="mt-1 block w-full" wire:model="state.theme_options.button.link" placeholder="eg. https://thehp.in" />
                    </div>
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
