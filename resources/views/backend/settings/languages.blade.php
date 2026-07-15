<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Languages") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You can manage languages here and add more languages if required. You can hit below button to update the language files.") }}
        <x-button id="update-language-files" class="mt-4 flex items-center gap-2" type="button" wire:click="updateLanguageFiles">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 animate-spin pause-spinner">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            {{ __("Update Language Files") }}
        </x-button>
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="language" value="{{ __('Default Language') }}" />
            <x-select class="mt-1 block w-full" wire:model.live="state.language">
                @foreach (($state["languages"] ?? []) as $language => $details)
                    <option value="{{ $language }}">{{ $details["label"] }}</option>
                @endforeach
            </x-select>
            <x-input-error for="state.language" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <label for="language_in_url" class="flex items-center cursor-pointer">
                <div class="block font-medium text-sm text-gray-700 dark:text-gray-300 mr-4">{{ __("Add Language Code in URL") }}</div>
                <x-toggle id="language_in_url" wire:model="state.language_in_url"></x-toggle>
            </label>
            <small>{{ __("If you enable this, then /{lang} slug is added to all your URLs.") }}</small>
        </div>
        <div class="col-span-6">
            <x-label class="mb-2" value="{{ __('Languages') }}" />
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                @foreach (($state["languages"] ?? []) as $language => $details)
                    <div class="flex gap-1">
                        @if ($details["is_active"])
                            <x-button-icon type="button" style="success" class="flex-1" wire:click="disableLanguage('{{ $language }}')" icon="hgi-checkmark-square-02" position="left">
                                {{ $details["label"] }}
                            </x-button-icon>
                        @else
                            <x-secondary-button-icon type="button" class="flex-1" wire:click="enableLanguage('{{ $language }}')" icon="hgi-cancel-square" position="left">
                                {{ $details["label"] }}
                            </x-secondary-button-icon>
                        @endif
                        <x-button-icon style="warning" type="button" wire:click="editTranslations('{{ $language }}')" icon="hgi-settings-02"></x-button-icon>
                        <x-button-icon type="button" wire:click="editLanguage('{{ $language }}')" icon="hgi-edit-03"></x-button-icon>
                        @if ($language != $state["language"])
                            <x-button-icon type="button" style="error" wire:click="deleteLanguage('{{ $language }}')" icon="hgi-delete-02"></x-button-icon>
                        @endif
                    </div>
                @endforeach
            </div>
            <x-button class="mt-5" type="button" wire:click="$set('showLanguageModal', true)">{{ __("Add") }}</x-button>
        </div>
        <x-dialog-modal wire:model.live="showLanguageModal">
            <x-slot name="title">
                {{ __("Add Language") }}
            </x-slot>

            <x-slot name="content">
                <div>
                    <x-label for="language">{{ __("Language Code") }}</x-label>
                    @if ($disableLanguageCode)
                        <x-input id="language" type="text" class="mt-1 block w-full" wire:model="form.language" placeholder="eg. en" disabled></x-input>
                    @else
                        <x-input id="language" type="text" class="mt-1 block w-full" wire:model="form.language" placeholder="eg. en"></x-input>
                    @endif
                    <x-input-error for="form.language" class="mt-2" />
                </div>
                <div class="mt-4">
                    <x-label for="label">{{ __("Label") }}</x-label>
                    <x-input id="label" type="text" class="mt-1 block w-full" wire:model="form.label" placeholder="eg. English"></x-input>
                    <x-input-error for="form.label" class="mt-2" />
                </div>
                <div class="mt-4">
                    <x-label for="type">{{ __("Type") }}</x-label>
                    <x-select class="mt-1 block w-full" wire:model="form.type">
                        <option value="ltr">{{ __("Left to Right (ltr)") }}</option>
                        <option value="rtl">{{ __("Right to Left (rtl)") }}</option>
                    </x-select>
                    <x-input-error for="form.type" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button type="button" wire:click="$set('showLanguageModal', false)" wire:loading.attr="disabled">
                    {{ __("Cancel") }}
                </x-secondary-button>

                <x-button type="button" class="ms-3" wire:click="addLanguage" wire:loading.attr="disabled">
                    {{ __("Add") }}
                </x-button>
            </x-slot>
        </x-dialog-modal>
        <x-dialog-modal maxWidth="5xl" wire:model.live="showTranslationModal">
            <x-slot name="title">
                {{ __("Manage Translations Strings") }}
            </x-slot>

            <x-slot name="content">
                <div class="grid grid-cols-2 gap-3 sticky top-0 bg-white dark:bg-gray-800 pb-2 z-10">
                    <h3>{{ __("English (en)") }}</h3>
                    <h3 class="text-right">{{ $state["languages"][$translations["language"]]["label"] ?? "" }} ({{ $translations["language"] }})</h3>
                </div>
                @foreach ($translations["strings"] as $key => $string)
                    <div class="flex items-center gap-3 mt-3">
                        <div class="grow">
                            <x-input type="text" class="block w-full" value="{{ $key }}" disabled readonly />
                        </div>
                        <i class="hgi hgi-stroke hgi-arrow-right-02"></i>
                        <div class="grow">
                            <x-input type="text" class="block w-full" wire:model="translations.strings.{{ $key }}" placeholder="{{ $string }}" />
                        </div>
                    </div>
                @endforeach
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button type="button" wire:click="$set('showTranslationModal', false)" wire:loading.attr="disabled">
                    {{ __("Cancel") }}
                </x-secondary-button>

                <x-button type="button" class="ms-3" wire:click="saveTranslations" wire:loading.attr="disabled">
                    {{ __("Save") }}
                </x-button>
            </x-slot>
        </x-dialog-modal>
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

@script
    <script>
        if (document.querySelector('#update-language-files')) {
            document.querySelector('#update-language-files').addEventListener('click', () => {
                document.querySelector('#update-language-files').disabled = true;
                document.querySelector('#update-language-files svg').classList.remove('pause-spinner');
            });
        }
        $wire.on('showSuccessMessageForLanguageFilesUpdated', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Language files updated successfully!',
                icon: 'success',
            });
        });
    </script>
@endscript
