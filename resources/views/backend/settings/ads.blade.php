<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Ad Spaces") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You can setup your Ad Codes here for various Ad Spaces on TMail.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <x-label for="ad_space_1" value="{{ __('Ad Space 1') }}" />
            <x-textarea id="ad_space_1" class="mt-4 block w-full resize-y" placeholder="Enter your Ad Code here" wire:model="state.ads.one"></x-textarea>
            <x-input-error for="ad_space_1" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="ad_space_2" value="{{ __('Ad Space 2') }}" />
            <x-textarea id="ad_space_2" class="mt-4 block w-full resize-y" placeholder="Enter your Ad Code here" wire:model="state.ads.two"></x-textarea>
            <x-input-error for="ad_space_2" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="ad_space_3" value="{{ __('Ad Space 3') }}" />
            <x-textarea id="ad_space_3" class="mt-4 block w-full resize-y" placeholder="Enter your Ad Code here" wire:model="state.ads.three"></x-textarea>
            <x-input-error for="ad_space_3" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="ad_space_4" value="{{ __('Ad Space 4') }}" />
            <x-textarea id="ad_space_4" class="mt-4 block w-full resize-y" placeholder="Enter your Ad Code here" wire:model="state.ads.four"></x-textarea>
            <x-input-error for="ad_space_4" class="mt-2" />
        </div>
        <div class="col-span-6 relative">
            <x-label for="ad_space_5" value="{{ __('Ad Space 5') }}" />
            <x-textarea id="ad_space_5" class="mt-4 block w-full resize-y" placeholder="Enter your Ad Code here" wire:model="state.ads.five"></x-textarea>
            <x-input-error for="ad_space_5" class="mt-2" />
            @if (config("app.settings.theme", "default") == "drax")
                <div class="absolute top-0 left-0 w-full h-full opacity-75 disabled-section text-gray-800 font-black text-center flex justify-center items-center">
                    {{ __("Ad Space 5 is not available in Drax Theme. Please switch to other theme to enable.") }}
                </div>
            @endif
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
