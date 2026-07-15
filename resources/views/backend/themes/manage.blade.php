<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($themes as $theme)
            <div class="shadow-md rounded-lg bg-white dark:bg-gray-800 p-3">
                <img class="rounded-lg max-w-full" src="{{ asset("themes/" . $theme . "/screenshot.jpg") }}" alt="" onerror="this.src='{{ asset("images/nopreview.jpg") }}'" />
                <div class="rounded-b-lg pt-3 flex justify-between">
                    <div class="flex items-center">
                        <span class="pr-2">{{ ucfirst($theme) }}{{ $this->version($theme) }}</span>
                        @if ($current === $theme)
                            <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-green-600 bg-green-200 uppercase last:mr-0 mr-1">
                                {{ __("Current") }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center">
                        @if ($current !== $theme)
                            <span wire:click="setTheme('{{ $theme }}')" class="text-xs cursor-pointer font-semibold inline-block py-1 px-3 rounded-full text-blue-600 bg-blue-200 last:mr-0 mr-1">
                                {{ __("Set as Current") }}
                            </span>
                            @if ($theme !== "default")
                                <span onclick="confirm('Are you sure? You cannnot reverse this action!') || event.stopImmediatePropagation()" wire:click="delete('{{ $theme }}')" class="text-xs cursor-pointer font-semibold inline-block py-1 px-3 rounded-full text-red-600 bg-red-200 last:mr-0 mr-1">
                                    <i class="hgi hgi-stroke hgi-delete-02"></i>
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @if (! config('app.demo', false))
        <x-form-section class="pt-20" submit="save">
            <x-slot name="title">
                {{ __("Add New Theme") }}
            </x-slot>

            <x-slot name="description">
                {{ __("You can add new Theme for TMail here.") }}
            </x-slot>

            <x-slot name="form">
                @if ($error)
                    <div class="col-span-6">
                        <div class="text-white px-6 py-4 border-0 rounded-lg relative bg-red-500">
                            <span class="inline-block align-middle mr-8">
                                <b class="capitalize">{{ __("Error") }}!</b>
                                {!! $error !!}
                            </span>
                        </div>
                    </div>
                @endif

                @if ($success)
                    <div class="col-span-6">
                        <div class="text-white px-6 py-4 border-0 rounded-lg relative bg-green-500">
                            <span class="inline-block align-middle mr-8">
                                <b class="capitalize">{{ __("Success") }}!</b>
                                {!! $success !!}
                            </span>
                        </div>
                    </div>
                @endif

                <div class="col-span-6 sm:col-span-4">
                    <x-label for="new" value="{{ __('Upload Zip') }}" />
                    <input id="new" type="file" class="mt-2" wire:model="new" />
                    <x-input-error for="new" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="actions">
                <div wire:loading.remove>
                    <x-button>
                        {{ __("Upload") }}
                    </x-button>
                </div>
                <div wire:loading>
                    <x-button disabled>
                        {{ __("Wait") }}
                    </x-button>
                </div>
            </x-slot>
        </x-form-section>
    @endif
</div>
