<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Export / Import") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You can export all the TMail settings using this. You can use this to restore the site settings if you plan to re-create the site from scatrch.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <x-label value="{{ __('Export Settings') }}" />
            <x-button class="mt-2" type="button" wire:click="export">{{ __("Export") }}</x-button>
        </div>
        <div class="col-span-6">
            <x-label value="{{ __('Import Settings') }}" />
            <small class="text-red-700 font-bold block mt-1">{{ __("Caution: This will override all the existing settings!") }}</small>
            <input class="block mt-2 text-xs" type="file" name="import" id="import" />
            <x-button id="import-settings" class="mt-2" type="button">{{ __("Import") }}</x-button>
        </div>
        <x-action-message class="mr-3" on="imported">
            {{ __("Imported.") }}
        </x-action-message>
    </x-slot>
</x-form-section>
