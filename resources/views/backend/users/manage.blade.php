<div>
    <div class="flex items-center justify-between gap-4 mb-5">
        <x-input size="sm" type="text" placeholder="{{ __('Search by Name or Email') }}" wire:model.debounce.300ms="filters.search" class="flex-1 w-64"></x-input>
        <x-select size="sm" wire:model="filters.role" class="w-48">
            <option value="">{{ __("All Statuses") }}</option>
            <option value="7">{{ __("Admin") }}</option>
            <option value="1">{{ __("Users") }}</option>
            <option value="0">{{ __("Suspended") }}</option>
        </x-select>
        <x-button type="button" wire:click="search"><i class="hgi hgi-stroke hgi-search-01 py-1"></i></x-button>
        @if ($filters != ["search" => "", "role" => null])
            <x-button style="error" type="button" wire:click="clearFilters"><i class="hgi hgi-stroke hgi-cancel-01 py-1"></i></x-button>
        @endif
    </div>
    <div class="space-y-5">
        <x-card class="flex flex-col">
            <div class="px-4 py-4 grid grid-cols-5 gap-5 border-b dark:border-gray-700 text-xs font-bold uppercase text-gray-500">
                <div class="col-span-2">{{ __("Name") }}</div>
                <div>{{ __("Role") }}</div>
                <div class="col-span-2">{{ __("Actions") }}</div>
            </div>
            @foreach ($users as $user)
                <div class="px-4 py-4 grid grid-cols-5 items-center gap-5 {{ $loop->first ? "" : "border-b" }}">
                    <div class="col-span-2 flex gap-4 items-center">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full" />
                        <div>
                            <div class="font-semibold">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="text-sm">
                        @if ($user->role == 0)
                            <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-900 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 ring-1 ring-gray-500/10 ring-inset">{{ __("Suspended") }}</span>
                        @elseif ($user->role == 1)
                            <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-900 px-2 py-1 text-xs font-medium text-green-600 dark:text-green-400 ring-1 ring-green-500/10 ring-inset">{{ __("User") }}</span>
                        @elseif ($user->role == 7)
                            <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900 px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 ring-1 ring-red-500/10 ring-inset">{{ __("Admin") }}</span>
                        @endif
                    </div>
                    <div class="col-span-2">
                        @if ($user->id === auth()->id())
                            <div class="text-xs">{{ __("You cannot perform actions on your own account.") }}</div>
                        @else
                            @if ($user->role == 0)
                                <x-button type="button" wire:click="userAction({{ $user->id }}, 'unsuspend')">{{ __("Unsuspend") }}</x-button>
                            @else
                                <x-button type="button" wire:click="userAction({{ $user->id }}, 'suspend')">{{ __("Suspend") }}</x-button>
                            @endif
                            <x-button type="button" style="error" wire:click="$dispatch('confirm-delete', '{{ $user->id }}')">
                                <i class="hgi hgi-stroke hgi-delete-02 pb-1"></i>
                            </x-button>
                        @endif
                    </div>
                </div>
            @endforeach
        </x-card>

        {{ $users->links() }}
    </div>
    @script
        <script>
            $wire.on('confirm-delete', (userId) => {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("You will not be able to recover this user!") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, delete it!") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.call('userAction', userId, 'delete');
                    }
                });
            });
        </script>
    @endscript
</div>
