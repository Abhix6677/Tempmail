<x-form-section submit="save">
    @if ($location == "primary")
        <x-slot name="title">
            {{ __("Primary Menu") }}
        </x-slot>
        <x-slot name="description">
            {{ __("For main pages like About, Blog, etc.") }}
        </x-slot>
    @elseif ($location == "secondary")
        <x-slot name="title">
            {{ __("Secondary Menu") }}
        </x-slot>
        <x-slot name="description">
            {{ __("For secondary pages like Terms, Privacy, etc.") }}
        </x-slot>
    @endif
    <x-slot name="form">
        @if ($translations)
            <div class="col-span-6 sm:col-span-4">
                <x-secondary-button class="mr-2" wire:click="clearAddUpdate">
                    <i class="hgi hgi-stroke hgi-arrow-left-02"></i>
                    <span class="ml-2">{{ __("Back") }}</span>
                </x-secondary-button>
            </div>
            @foreach ($translations as $language => $translation)
                @if ($language !== config("app.settings.language"))
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="name_{{ $language }}" value="{{ __('Name') }} ({{ $language }})" />
                        <x-input id="name_{{ $language }}" type="text" class="mt-1 block w-full" placeholder="Menu Item Name" wire:model="translations.{{ $language }}" />
                    </div>
                @endif
            @endforeach
        @elseif ($addMenuItem || $updateMenuItem)
            <div class="col-span-6">
                <x-secondary-button class="mr-2" wire:click="clearAddUpdate">
                    <i class="hgi hgi-stroke hgi-arrow-left-02"></i>
                    <span class="ml-2">{{ __("Back") }}</span>
                </x-secondary-button>
            </div>
            <div class="col-span-6">
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" type="text" class="mt-1 block w-full" placeholder="Menu Item Name" wire:model="menu.name" />
                <x-input-error for="menu.name" class="mt-2" />
            </div>
            @if ($updateMenuItem)
                <div class="col-span-6">
                    <x-button wire:click="showTranslations()" type="button" style="warning" class="flex gap-3 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                        </svg>
                        <span>{{ __("Translate") }}</span>
                    </x-button>
                </div>
            @endif

            @if ($showParent)
                <div class="col-span-6">
                    <x-label for="link" value="{{ __('Link') }}" />
                    <x-input id="link" type="text" class="mt-1 block w-full" placeholder="Menu Item Link" wire:model="menu.link" />
                    <x-input-error for="menu.link" class="mt-2" />
                    <small>{{ __("Add URLs without language codes. TMail will handle the localization.") }}</small>
                </div>
                <div class="col-span-6">
                    <label for="new_tab" class="flex items-center cursor-pointer">
                        <x-label for="new_tab" class="mr-4">{{ __("Open in New Tab") }}</x-label>
                        <x-toggle id="new_tab" wire:model="menu.target"></x-toggle>
                    </label>
                </div>
                @if ($location == "primary")
                    <div class="col-span-6">
                        <x-label for="parent" value="{{ __('Parent') }} " />
                        <div class="relative">
                            <x-select class="mt-1 block w-full" wire:model="menu.parent_id">
                                <option value="0">None</option>
                                @foreach ($menus as $m)
                                    @if (($addMenuItem && $m->parent_id === null) || ($updateMenuItem && $m->id !== $menu["id"] && $m->parent_id === null))
                                        <option value="{{ $m->id }}">{{ $m->name }} - #{{ $m->id }}</option>
                                    @endif
                                @endforeach
                            </x-select>
                        </div>
                        <x-input-error for="parent" class="mt-2" />
                    </div>
                @endif
            @else
                <div class="col-span-6">
                    <em class="text-sm text-gray-400">{{ __("Other fields are disabled as this Menu Item has child Items") }}</em>
                </div>
            @endif
        @else
            <div class="col-span-6 -mt-4">
                @if (count($menus) == 0)
                    <div class="mt-4 text-sm text-center text-gray-500">{{ __("No Menu Items") }}</div>
                @endif

                @foreach ($menus as $menu)
                    @if ($menu->parent_id === null)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl px-5 py-3 mt-3 flex justify-between items-center {{ $menu->status === 0 ? "opacity-50" : "" }}">
                            <div class="flex items-center">
                                <div class="flex flex-col">
                                    <i wire:click="moveUp({{ $menu }})" class="hgi hgi-stroke hgi-arrow-up-01 cursor-pointer"></i>
                                    <i wire:click="moveDown({{ $menu }})" class="hgi hgi-stroke hgi-arrow-down-01 cursor-pointer"></i>
                                </div>
                                <div class="ml-5 flex flex-col">
                                    <div>
                                        {{ $menu->name }}
                                        <small>{!! $menu->target === "_blank" ? '<i class="hgi hgi-stroke hgi-share-05"></i>' : "" !!}</small>
                                    </div>
                                    <div><small classs="text-xs">{{ $menu->link }}</small></div>
                                </div>
                                <div class="ml-5"></div>
                            </div>
                            <div class="flex space-x-3 text-xl">
                                <x-button-icon icon="hgi-edit-03" position="left" type="button" wire:click="showUpdate({{ $menu }})">{{ __("Edit") }}</x-button-icon>
                                @if ($menu->status)
                                    <x-button-icon style="warning" icon="hgi-view-off-slash" position="left" type="button" wire:click="toggleStatus({{ $menu }})"></x-button-icon>
                                @else
                                    <x-button-icon style="success" icon="hgi-view" position="left" type="button" wire:click="toggleStatus({{ $menu }})"></x-button-icon>
                                @endif
                                <x-button-icon style="error" icon="hgi-delete-01" position="left" type="button" wire:click="$dispatch('confirm-delete', '{{ $menu->id }}')"></x-button-icon>
                            </div>
                        </div>
                        @foreach ($menu->getChildAll() as $child)
                            <div class="flex items-center gap-1">
                                <div class="text-2xl mt-3 text-gray-200 dark:text-gray-700"><i class="hgi hgi-stroke hgi-arrow-move-down-right"></i></div>
                                <div class="flex-1 border border-gray-200 dark:border-gray-700 rounded px-5 py-3 mt-3 flex justify-between items-center {{ $child->status === 0 ? "opacity-50" : "" }}">
                                    <div class="flex items-center">
                                        <div class="flex flex-col">
                                            <i wire:click="moveUp({{ $child }})" class="hgi hgi-stroke hgi-arrow-up-01 cursor-pointer"></i>
                                            <i wire:click="moveDown({{ $child }})" class="hgi hgi-stroke hgi-arrow-down-01 cursor-pointer"></i>
                                        </div>
                                        <div class="ml-5 flex flex-col">
                                            <div>
                                                {{ $child->name }}
                                                <small>{!! $child->target === "_blank" ? '<i class="hgi hgi-stroke hgi-share-05"></i>' : "" !!}</small>
                                            </div>
                                            <div><small classs="text-xs">{{ $child->link }}</small></div>
                                        </div>
                                        <div class="ml-5"></div>
                                    </div>
                                    <div class="flex space-x-3 text-xl">
                                        <x-button-icon icon="hgi-edit-03" position="left" type="button" wire:click="showUpdate({{ $child }})">{{ __("Edit") }}</x-button-icon>
                                        @if ($child->status)
                                            <x-button-icon style="warning" icon="hgi-view-off-slash" position="left" type="button" wire:click="toggleStatus({{ $child }})"></x-button-icon>
                                        @else
                                            <x-button-icon style="success" icon="hgi-view" position="left" type="button" wire:click="toggleStatus({{ $child }})"></x-button-icon>
                                        @endif
                                        <x-button-icon style="error" icon="hgi-delete-01" position="left" type="button" wire:click="$dispatch('confirm-delete', '{{ $child->id }}')"></x-button-icon>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        @endif
        @script
            <script>
                $wire.on('confirm-delete', (menuId) => {
                    Swal.fire({
                        title: '{{ __("Are you sure?") }}',
                        text: '{{ __("You will not be able to recover this!") }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: '{{ __("Yes, delete it!") }}',
                        cancelButtonText: '{{ __("Cancel") }}',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.call('delete', menuId);
                        }
                    });
                });
            </script>
        @endscript
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __("Saved.") }}
        </x-action-message>
        @if ($translations)
            <x-button type="button" wire:click="saveTranslations">
                {{ __("Save Translations") }}
            </x-button>
        @elseif ($addMenuItem || $updateMenuItem)
            @if ($addMenuItem)
                <x-button type="button" wire:click="saveMenu">
                    {{ __("Add") }}
                </x-button>
            @else
                <x-button type="button" wire:click="saveMenu">
                    {{ __("Update") }}
                </x-button>
            @endif
        @else
            <x-button type="button" style="success" wire:click="$toggle('addMenuItem')">
                {{ __("Add Item") }}
            </x-button>
        @endif
    </x-slot>
</x-form-section>
