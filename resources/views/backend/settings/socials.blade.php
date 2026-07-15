<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Socials") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You can add multiple social media properties to showcase on your website. You can select icons for social media from Font Awesome by visiting ") }}
        <a class="font-bold underline" href="https://fontawesome.com/v5/search?ic=free&o=r" target="_blank" rel="noopener noreferrer">{{ __("this link.") }}</a>
    </x-slot>

    <x-slot name="form">
        @foreach (($state["socials"] ?? []) as $key => $social)
            <div x-data="{ icon: '{{ $social["icon"] }}' }" class="col-span-6 flex">
                <div>
                    <x-label value="{{ __('Icon') }}" />
                    <div class="relative">
                        <x-input x-model="icon" type="text" class="mt-1 block" placeholder="fas fa-x" wire:model="state.socials.{{ $key }}.icon" />
                        <div class="absolute inset-y-0 right-0 flex items-center px-3"><i :class="icon"></i></div>
                    </div>
                    <x-input-error for="state.socials.{{ $key }}.icon" class="mt-2" />
                </div>
                <div class="flex-1 ml-3">
                    <x-label value="{{ __('Link') }}" />
                    <div class="flex">
                        <x-input type="text" class="mt-1 block w-full" placeholder="https://facebook.com/yourtmail" wire:model="state.socials.{{ $key }}.link" />
                        <button type="button" wire:click="remove({{ $key }})" class="form-input rounded-md ml-3 mt-1 bg-red-700 text-white border-0"><i class="hgi hgi-stroke hgi-delete-02"></i></button>
                    </div>
                    <x-input-error for="state.socials.{{ $key }}.link" class="mt-2" />
                </div>
            </div>
        @endforeach

        <div class="col-span-6 {{ count($state["socials"] ?? []) > 0 ? "-mt-3" : "" }}">
            <x-button type="button" wire:click="add">{{ __("Add") }}</x-button>
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
