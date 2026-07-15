<x-form-section submit="save">
    <x-slot name="title">
        {{ __("General") }}
    </x-slot>

    <x-slot name="description">
        {{ __("All the general settings shown here are applied on overall website.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('App Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" />
            <x-input-error for="state.name" class="mt-2" />
        </div>
        <div x-data="{ show: false }" class="col-span-6 sm:col-span-4">
            <x-label for="license_key" value="{{ __('License Key') }}" />
            <div class="relative">
                <x-input id="license_key" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full pr-28" wire:model="state.license_key" />
                <div class="flex items-center gap-2 absolute inset-y-0 right-0 uppercase text-xs mr-3">
                    <div x-on:click="show = !show" x-text="show ? 'HIDE' : 'SHOW'" class="cursor-pointer"></div>
                </div>
            </div>
        </div>
        @if (! config('app.demo', false))
            <div class="col-span-6 sm:col-span-3">
                <x-label for="logo" value="{{ __('Logo') }}" />
                <input class="mt-2" type="file" wire:model="logo" accept="image/*" />

                @if ($logo)
                    <img class="max-w-56 rounded my-2 p-2 striped-img-preview" src="{{ $logo->temporaryUrl() }}" />
                @elseif ($state["custom_logo"])
                    <img class="max-w-56 rounded my-2 p-2 striped-img-preview" src="{{ $state["custom_logo"] }}" />
                @else
                    <img class="max-w-56 rounded my-2 p-2 striped-img-preview" src="{{ asset("images/logo.png") }}" />
                @endif
                <x-input-error for="logo" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-3">
                <x-label for="favicon" value="{{ __('Favicon') }}" />
                <input class="mt-2" type="file" wire:model="favicon" accept="image/*" />

                @if ($favicon)
                    <img class="max-w-16 rounded my-2 p-2 striped-img-preview" src="{{ $favicon->temporaryUrl() }}" />
                @elseif ($state["custom_favicon"])
                    <img class="max-w-16 rounded my-2 p-2 striped-img-preview" src="{{ $state["custom_favicon"] }}" />
                @else
                    <img class="max-w-16 rounded my-2 p-2 striped-img-preview" src="{{ asset("images/icon.png") }}" />
                @endif
                <x-input-error for="favicon" class="mt-2" />
            </div>
        @endif

        <div x-data="{
            userRegistrationEnabled:
                {{ ($state['user_registration']['enabled'] ?? false) ? 'true' : 'false' }},
        }" class="col-span-6">
            <div>
                <label for="enable_user_registration" class="flex items-center cursor-pointer">
                    <div class="block font-medium text-sm text-gray-700 dark:text-gray-300 mr-4">{{ __("Enable User Registration") }}</div>
                    <x-toggle x-model="userRegistrationEnabled" id="enable_user_registration" wire:model="state.user_registration.enabled"></x-toggle>
                </label>
                <small>{{ __("Once enabled, your visitors can register an account with you.") }}</small>
            </div>

            <div x-show="userRegistrationEnabled" class="mt-4">
                <label for="require_email_verification" class="flex items-center cursor-pointer">
                    <div class="block font-medium text-sm text-gray-700 dark:text-gray-300 mr-4">{{ __("Require Email Verification") }}</div>
                    <x-toggle id="require_email_verification" wire:model="state.user_registration.require_email_verification"></x-toggle>
                </label>
                <small>{{ __("If enabled, users will need to verify their email address before they can log in.") }}</small>
            </div>
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="homepage" value="{{ __('Homepage') }}" />
            <x-select class="mt-1 block w-full" wire:model.live="state.homepage">
                <option value="0">App - TMail</option>
                @foreach ($state["pages"] as $id => $page)
                    <option value="{{ $id }}">{{ $page }}</option>
                @endforeach
            </x-select>
            <x-input-error for="state.homepage" class="mt-2" />
        </div>
        @if ($state["homepage"] == 0)
            <div class="col-span-6 sm:col-span-4">
                <label for="disable_mailbox_slug" class="flex items-center cursor-pointer">
                    <div class="block font-medium text-sm text-gray-700 dark:text-gray-300 mr-4">{{ __("Disable Mailbox Slug from URL") }}</div>
                    <x-toggle id="disable_mailbox_slug" wire:model="state.disable_mailbox_slug"></x-toggle>
                </label>
                <small>{{ __("If you enable this, then /mailbox slug is removed from your URL.") }}</small>
            </div>
        @endif

        <div class="col-span-6">
            <div class="flex">
                <div x-data="{ color: '{{ $state["colors"]["primary"] ?? '#3b82f6' }}' }" class="flex-1">
                    <x-label value="{{ __('Primary Color') }}" />
                    <div class="relative">
                        <label for="primary_color">
                            <div x-bind:style="`background-color: ${color}`" class="mt-1 rounded-md cursor-pointer h-6 w-20"></div>
                        </label>
                        <input x-model="color" id="primary_color" type="color" class="absolute top-0 left-0 invisible" wire:model="state.colors.primary" />
                    </div>
                    <x-input-error for="primary_color" class="mt-2" />
                </div>
                <div x-data="{ color: '{{ $state["colors"]["secondary"] ?? '#64748b' }}' }" class="flex-1">
                    <x-label for="secondary_color" value="{{ __('Secondary Color') }}" />
                    <div class="relative">
                        <label for="secondary_color">
                            <div x-bind:style="`background-color: ${color}`" class="mt-1 rounded-md cursor-pointer h-6 w-20"></div>
                        </label>
                        <input x-model="color" id="secondary_color" type="color" class="absolute top-0 left-0 invisible" wire:model="state.colors.secondary" />
                    </div>
                    <x-input-error for="secondary_color" class="mt-2" />
                </div>
                <div x-data="{ color: '{{ $state["colors"]["tertiary"] ?? '#000000' }}' }" class="flex-1">
                    <x-label for="tertiary_color" value="{{ __('Tertiary Color') }}" />
                    <div class="relative">
                        <label for="tertiary_color">
                            <div x-bind:style="`background-color: ${color}`" class="mt-1 rounded-md cursor-pointer h-6 w-20"></div>
                        </label>
                        <input x-model="color" id="tertiary_color" type="color" class="absolute top-0 left-0 invisible" wire:model="state.colors.tertiary" />
                    </div>
                    <x-input-error for="tertiary_color" class="mt-2" />
                </div>
            </div>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <label for="enable_dark_mode" class="flex items-center cursor-pointer">
                <div class="block font-medium text-sm text-gray-700 dark:text-gray-300 mr-4">{{ __("Enable Dark Mode") }}</div>
                <x-toggle id="enable_dark_mode" wire:model="state.enable_dark_mode"></x-toggle>
            </label>
        </div>
        <div x-data="{
            cookie: {{ $state["cookie"]["enable"] ? "true" : "false" }},
        }" class="col-span-6 sm:col-span4">
            <label for="cookie_input" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Cookie Policy") }}</x-label>
                <x-toggle x-model="cookie" wire:model="state.cookie.enable" id="cookie_input"></x-toggle>
            </label>
            <x-textarea x-show="cookie" class="mt-4 block w-full resize-y" placeholder="Enter the Text to show for Cookie Policy (HTML allowed)" wire:model="state.cookie.text"></x-textarea>
            <x-input-error for="state.cookie" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <label for="enable_create_from_url" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Enable Mail ID Creation from URL") }}</x-label>
                <x-toggle id="enable_create_from_url" wire:model="state.enable_create_from_url"></x-toggle>
            </label>
            <small>{{ __("If you enable this, then users will be able to create email ID from URL.") }}</small>
        </div>
        <div class="col-span-6">
            <x-label for="app_header" value="{{ __('Custom Header for App (MailBox)') }}" />
            <x-textarea id="app_header" class="mt-1 block w-full resize-y" placeholder="Enter your HTML Code here" wire:model="state.app_header"></x-textarea>
            <x-input-error for="app_header" class="mt-2" />
            <small>{{ __("Here you can add any Meta Tags or any additional custom header tags for App Page") }}</small>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="language" value="{{ __('External Link Masking Service') }}" />
            <div class="relative">
                <x-select class="mt-1 block w-full" wire:model.live="state.external_link_masker">
                    <option value="">{{ __("Disabled") }}</option>
                    <option value="https://relink.cc">{{ __("relink.cc") }}</option>
                    <option value="custom">{{ __("Custom") }}</option>
                </x-select>
                @if ($state["external_link_masker"] == "custom")
                    <x-input id="custom_external_link_masker" type="text" class="mt-1 block w-full" wire:model="state.custom_external_link_masker" placeholder="eg. https://relink.cc" />
                @endif
            </div>
            <x-input-error for="state.language" class="mt-2" />
            <small>{{ __("TMail will use this to remove your site footprint being passed-on to external link.") }}</small>
        </div>
        <div id="ad-block-detector" class="col-span-6">
            <label for="enable_ad_block_detector" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Enable Ad Block Detector") }}</x-label>
                <x-toggle id="enable_ad_block_detector" wire:model="state.enable_ad_block_detector"></x-toggle>
            </label>
            <small>{{ __("If you enable this, then TMail block all the users from using TMail that have Ad Blocker enabled.") }}</small>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <div class="mb-4">
                <x-label value="{{ __('Font Family') }}" />
                <small>
                    {{ __("Use") }}
                    <a href="https://fonts.google.com/" class="underline" target="_blank" rel="noopener noreferrer">{{ __("Google Fonts") }}</a>
                    {{ __("with exact name.") }}
                </small>
            </div>
            <div class="mb-2">
                <small>{{ __("Head") }}</small>
                <small class="text-gray-500">- {{ __("Applied on heading tags h1, h2, h3, h4, h5 and h6") }}</small>
                <x-input id="font_family" type="text" class="mt-1 block w-full" min="10" wire:model="state.font_family.head" />
                <x-input-error for="state.font_family.head" class="mt-2" />
            </div>
            <div>
                <small>{{ __("Body") }}</small>
                <small class="text-gray-500">- {{ __("Applied on body on other tags like p, div, a, etc.") }}</small>
                <x-input type="text" class="mt-1 block w-full" min="10" wire:model="state.font_family.body" />
                <x-input-error for="state.font_family.body" class="mt-2" />
            </div>
        </div>
        <div x-data="{
            disqus: {{ $state["disqus"]["enable"] ? "true" : "false" }},
        }" class="col-span-6 sm:col-span4">
            <div>
                <label for="disqus_input" class="flex items-center cursor-pointer">
                    <x-label class="mr-4">{{ __("Disqus Integration") }}</x-label>
                    <x-toggle x-model="disqus" wire:model="state.disqus.enable" id="disqus_input"></x-toggle>
                </label>
                <small>{{ __("If you enable this, Disqus will be shown after every Blog Post on TMail.") }}</small>
            </div>
            <div class="mt-4" x-show="disqus">
                <x-label value="{{ __('Disqus Shortname') }}" />
                <x-input class="mt-1 block w-full" placeholder="eg. tmail-1" wire:model="state.disqus.shortname"></x-input>
                <x-input-error for="state.disqus" class="mt-2" />
            </div>
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
