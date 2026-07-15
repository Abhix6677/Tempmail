<div>
    <form wire:submit.prevent="save">
        <x-dialog-modal wire:model="showDomainModal">
            <x-slot name="title">
                {{ isset($domain["id"]) && $domain["id"] ? __("Update Domain") : __("Add Domain") }}
            </x-slot>
            <x-slot name="content">
                <div class="flex flex-col gap-4">
                    <div>
                        <x-label for="domain" value="{{ __('Domain') }}" />
                        <x-input id="domain" type="text" class="mt-1 block w-full" wire:model="domain.domain" />
                        <x-input-error for="domain.domain" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="type" value="{{ __('Type') }}" />
                        <x-select class="mt-1 block w-full" wire:model="domain.type">
                            <option value="open">{{ __("Open") }}</option>
                            <option value="member">{{ __("Member") }}</option>
                        </x-select>
                        <x-input-error for="domain.type" class="mt-2" />
                        <small>{{ __("Member only domains require user registration.") }}</small>
                    </div>
                    <div>
                        <label for="is_active" class="flex items-center cursor-pointer">
                            <div class="block font-medium text-sm text-gray-700 dark:text-gray-300 mr-4">{{ __("Enable") }}</div>
                            <x-toggle id="is_active" wire:model="domain.is_active"></x-toggle>
                        </label>
                    </div>
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-secondary-button type="button" wire:click="$set('showDomainModal', false)">
                    {{ __("Cancel") }}
                </x-secondary-button>
                <x-button type="submit" class="ml-2">
                    @if (isset($domain["id"]) && $domain["id"])
                        {{ __("Update") }}
                    @else
                        {{ __("Add") }}
                    @endif
                </x-button>
            </x-slot>
        </x-dialog-modal>
    </form>
    <div class="flex justify-end -mt-24 mb-16 pr-5 md:pr-0">
        <x-button type="button" wire:click="openDomainModal">{{ __("Add Domain") }}</x-button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($domains as $domain)
            <x-card class="overflow-hidden p-6">
                <div class="flex items-center gap-2 text-xs mb-4">
                    <h3 class="flex-1 text-lg font-semibold line-clamp-2">{{ $domain->domain }}</h3>

                    @if ($domain->type == "open")
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-900 text-xs font-medium rounded-full">{{ __("Open") }}</span>
                    @elseif ($domain->type == "member")
                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-xs font-medium rounded-full">{{ __("Member") }}</span>
                    @elseif ($domain->type == "premium")
                        <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-xs font-medium rounded-full">{{ __("Premium") }}</span>
                    @endif

                    @if ($domain->is_active)
                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-xs font-medium rounded-full">{{ __("Active") }}</span>
                    @else
                        <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900 text-xs font-medium rounded-full">{{ __("Inactive") }}</span>
                    @endif
                </div>
                <div class="flex gap-2">
                    <x-button-icon class="flex-1 justify-center" icon="hgi-edit-03" position="left" type="button" wire:click="openDomainModal({{ $domain->id }})">{{ __("Edit") }}</x-button-icon>
                    <x-button-icon class="justify-center" style="error" icon="hgi-delete-01" position="left" type="button" wire:click="$dispatch('confirm-delete', '{{ $domain->id }}')"></x-button-icon>
                </div>
            </x-card>
        @endforeach
    </div>

    @script
        <script>
            $wire.on('confirm-delete', (domainId) => {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("You will not be able to recover this domain!") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, delete it!") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.call('delete', domainId);
                    }
                });
            });
        </script>
    @endscript
</div>
