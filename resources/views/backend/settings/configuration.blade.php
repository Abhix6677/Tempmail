<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Configuration") }}
    </x-slot>

    <x-slot name="description">
        {{ __("TMail specific settings which are applied on the App functionality.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="default_domain" value="{{ __('Default Domain') }}" />
            <div class="relative">
                <x-select class="mt-1 block w-full" wire:model="state.default_domain">
                    <option value="0">{{ __("None") }}</option>
                    @foreach ($domains as $domain)
                        @if ($domain->is_active)
                            <option value="{{ $domain->id }}">{{ $domain->domain }}</option>
                        @endif
                    @endforeach
                </x-select>
            </div>
            <x-input-error for="state.after_last_email_delete" class="mt-2" />
            <small>{{ __("Pre-selected domain in the dropdown while your user creates the email IDs.") }}</small>
        </div>
        <div class="col-span-6">
            <label for="add_mail_in_title" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Add Mail ID to Page Title") }}</x-label>
                <x-toggle id="add_mail_in_title" wire:model="state.add_mail_in_title"></x-toggle>
            </label>
            <small>{{ __("If you enable this, then TMail will automatically add the created temp mail to the page title.") }}</small>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="fetch_seconds" value="{{ __('Fetch Seconds') }}" />
            <x-input id="fetch_seconds" type="number" class="mt-1 block w-full" min="10" wire:model="state.fetch_seconds" />
            <x-input-error for="state.fetch_seconds" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email_limit" value="{{ __('Email Limit') }}" new="true" />
            <x-input id="email_limit" type="number" class="mt-1 block w-full" wire:model="state.email_limit" />
            <x-input-error for="state.email_limit" class="mt-2" />
            <small>{{ __("Limit on number of email ids that can be created per IP address in 24 hours. Recommended - 5.") }}</small>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="fetch_messages_limit" value="{{ __('Fetch Messages Limit') }}" />
            <x-input id="fetch_messages_limit" type="number" class="mt-1 block w-full" wire:model="state.fetch_messages_limit" />
            <x-input-error for="state.fetch_messages_limit" class="mt-2" />
            <small>{{ __("Limit of messages retrived at a time. Keep it to as low as possible. Default - 15.") }}</small>
        </div>
        <div x-data="{ show: false }" class="col-span-6 sm:col-span-4">
            <x-label for="cron_password" value="{{ __('CRON Password') }}" />
            <div class="relative">
                <x-input id="cron_password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full" autocomplete="new-password" wire:model="state.cron_password" />
                <div x-on:click="show = !show" x-text="show ? 'HIDE' : 'SHOW'" class="cursor-pointer absolute inset-y-0 right-0 flex items-center px-5 text-xs"></div>
            </div>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <div class="flex">
                <div>
                    <x-label for="cron_password" value="{{ __('Delete After') }}" />
                    <x-input type="number" class="mt-1 block w-full" wire:model="state.delete.value" />
                </div>
                <div class="ml-2 flex-1">
                    <x-label for="cron_password" value="{{ __('Delete Duration') }}" />
                    <div class="relative">
                        <x-select class="mt-1 block w-full" wire:model="state.delete.key">
                            @if (config("app.settings.engine") == "delivery")
                                <option value="m">{{ __("Min(s)") }}</option>
                                <option value="h">{{ __("Hour(s)") }}</option>
                            @endif

                            <option value="d">{{ __("Day(s)") }}</option>
                            <option value="w">{{ __("Week(s)") }}</option>
                            <option value="M">{{ __("Month(s)") }}</option>
                        </x-select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label value="{{ __('Forbidden IDs') }}" />
            @foreach ($state["forbidden_ids"] as $key => $value)
                <div class="flex {{ $key > 0 ? "mt-1" : "" }}">
                    <x-input type="text" class="mt-1 block w-full" wire:model="state.forbidden_ids.{{ $key }}" />
                    <button type="button" wire:click="remove('forbidden_ids', {{ $key }})" class="form-input rounded-md ml-2 mt-1 bg-red-700 text-white border-0"><i class="hgi hgi-stroke hgi-delete-02"></i></button>
                </div>
                <x-input-error for="state.forbidden_ids.{{ $key }}" class="mt-1 mb-2" />
            @endforeach

            <x-button class="mt-2" type="button" wire:click="add('forbidden_ids')">{{ __("Add") }}</x-button>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label value="{{ __('Blocked Domains') }}" />
            <small class="block">{{ __("Emails from this domain(s) will be marked as BLOCKED on the site") }}</small>
            @foreach ($state["blocked_domains"] as $key => $value)
                <div class="flex {{ $key > 0 ? "mt-1" : "" }}">
                    <x-input type="text" class="mt-1 block w-full" wire:model="state.blocked_domains.{{ $key }}" />
                    <button type="button" wire:click="remove('blocked_domains', {{ $key }})" class="form-input rounded-md ml-2 mt-1 bg-red-700 text-white border-0"><i class="hgi hgi-stroke hgi-delete-02"></i></button>
                </div>
                <x-input-error for="state.blocked_domains.{{ $key }}" class="mt-1 mb-2" />
            @endforeach

            <x-button class="mt-2" type="button" wire:click="add('blocked_domains')">{{ __("Add") }}</x-button>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label value="{{ __('Allowed Domains') }}" />
            <small class="block">
                <span class="font-bold text-red-500">{{ __("Caution: ") }}</span>
                {{ __("Only emails from this domain(s) will be allowed on the site, all others will be blocked by default.") }}
            </small>
            @foreach ($state["allowed_domains"] as $key => $value)
                <div class="flex {{ $key > 0 ? "mt-1" : "" }}">
                    <x-input type="text" class="mt-1 block w-full" wire:model="state.allowed_domains.{{ $key }}" />
                    <button type="button" wire:click="remove('allowed_domains', {{ $key }})" class="form-input rounded-md ml-2 mt-1 bg-red-700 text-white border-0"><i class="hgi hgi-stroke hgi-delete-02"></i></button>
                </div>
                <x-input-error for="state.allowed_domains.{{ $key }}" class="mt-1 mb-2" />
            @endforeach

            <x-button class="mt-2" type="button" wire:click="add('allowed_domains')">{{ __("Add") }}</x-button>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="date_format" value="{{ __('Date Format') }}" />
            <x-input id="date_format" type="text" class="mt-1 block w-full" wire:model="state.date_format" />
            <x-input-error for="state.date_format" class="mt-2" />
            <small>
                <span class="font-bold text-red-500">{{ __("Caution: ") }}</span>
                {{ __("For Advance Users Only!") }}
            </small>
            <small><a class="border-b" href="https://www.w3schools.com/php/func_date_date_format.asp" target="_blank">{{ __("View Reference") }}</a></small>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="after_last_email_delete" value="{{ __('Action after last Email ID is Deleted by User') }}" />
            <x-select class="mt-1 block w-full" wire:model="state.after_last_email_delete">
                <option value="redirect_to_homepage">{{ __("Redirect to Homepage") }}</option>
                <option value="create_new_email_id">{{ __("Create New Email ID") }}</option>
            </x-select>
            <x-input-error for="state.after_last_email_delete" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <div class="flex">
                <div class="flex-1">
                    <x-label for="custom_min" value="{{ __('Custom Min Length') }}" />
                    <x-input id="custom_min" type="number" min="3" class="mt-1 block w-full" wire:model="state.custom.min" />
                    <x-input-error for="state.custom.min" class="mt-1 mb-2" />
                </div>
                <div class="flex-1 ml-2">
                    <x-label for="custom_max" value="{{ __('Custom Max Length') }}" />
                    <x-input id="custom_max" type="number" class="mt-1 block w-full" wire:model="state.custom.max" />
                    <x-input-error for="state.custom.max" class="mt-1 mb-2" />
                </div>
            </div>
            <small>{{ __("Above are character limits for username on custom email address.") }}</small>
        </div>
        <div x-data="{
            show_advance_random: {{ $state["advance_random"] ? "true" : "false" }},
        }" class="col-span-6 sm:col-span-4">
            <label for="show_advance_random" class="flex items-center cursor-pointer">
                <x-label class="mr-4">{{ __("Show Advance Random Email Configuration") }}</x-label>
                <x-toggle id="show_advance_random" wire:model="state.advance_random"></x-toggle>
            </label>
            <div x-show="show_advance_random" class="mt-6">
                <div class="flex">
                    <div class="flex-1">
                        <x-label for="random_start" value="{{ __('Random Start') }}" />
                        <x-input id="random_start" type="text" class="mt-1 block w-full" wire:model="state.random.start" />
                        <x-input-error for="state.random.start" class="mt-1 mb-2" />
                    </div>
                    <div class="flex-1 ml-2">
                        <x-label for="random_end" value="{{ __('Random End') }}" />
                        <x-input id="random_end" type="text" class="mt-1 block w-full" wire:model="state.random.end" />
                        <x-input-error for="state.random.end" class="mt-1 mb-2" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-6">
            <label for="disable_used_email" class="flex items-center cursor-pointer">
                <x-label class="mr-4" for="email_limit" value="{{ __('Disable Used Email') }}" new="true" />
                <x-toggle id="disable_used_email" wire:model="state.disable_used_email"></x-toggle>
            </label>
            <small>{{ __("If you enable this, same email address cannot be created by user from different IP for one week.") }}</small>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="allow_reuse_email_in_days" value="{{ __('Release Used Email (Days)') }}" new="true" />
            <x-input id="allow_reuse_email_in_days" type="number" class="mt-1 block w-full" wire:model="state.allow_reuse_email_in_days" />
            <x-input-error for="state.allow_reuse_email_in_days" class="mt-2" />
            <small>{{ __("Number of days after which used email ID is available to re-use.") }}</small>
        </div>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="captcha" value="{{ __('Captcha') }}" />
            <div class="relative">
                <x-select class="mt-1 block w-full" wire:model.live="state.captcha">
                    <option value="off">{{ __("Disabled") }}</option>
                    <option value="recaptcha2">reCaptcha v2</option>
                    <option value="recaptcha3">reCaptcha v3</option>
                    <option value="hcaptcha">hCaptcha</option>
                </x-select>
            </div>
            @if ($state["captcha"] == "recaptcha2")
                <div class="mt-6">
                    <div>
                        <x-label for="recaptcha2_site_key" value="{{ __('Site Key') }}" />
                        <x-input id="recaptcha2_site_key" type="text" class="mt-1 block w-full" wire:model="state.recaptcha2.site_key" />
                        <x-input-error for="state.recaptcha2.site_key" class="mt-1 mb-2" />
                    </div>
                    <div class="mt-2">
                        <x-label for="recaptcha2_secret_key" value="{{ __('Secret Key') }}" />
                        <x-input id="recaptcha2_secret_key" type="text" class="mt-1 block w-full" wire:model="state.recaptcha2.secret_key" />
                        <x-input-error for="state.recaptcha2.secret_key" class="mt-1 mb-2" />
                    </div>
                </div>
            @elseif ($state["captcha"] == "recaptcha3")
                <div class="mt-6">
                    <div>
                        <x-label for="recaptcha3_site_key" value="{{ __('Site Key') }}" />
                        <x-input id="recaptcha3_site_key" type="text" class="mt-1 block w-full" wire:model="state.recaptcha3.site_key" />
                        <x-input-error for="state.recaptcha3.site_key" class="mt-1 mb-2" />
                    </div>
                    <div class="mt-2">
                        <x-label for="recaptcha3_secret_key" value="{{ __('Secret Key') }}" />
                        <x-input id="recaptcha3_secret_key" type="text" class="mt-1 block w-full" wire:model="state.recaptcha3.secret_key" />
                        <x-input-error for="state.recaptcha3.secret_key" class="mt-1 mb-2" />
                    </div>
                </div>
            @elseif ($state["captcha"] == "hcaptcha")
                <div class="mt-6">
                    <div>
                        <x-label for="hcaptcha_site_key" value="{{ __('Site Key') }}" />
                        <x-input id="hcaptcha_site_key" type="text" class="mt-1 block w-full" wire:model="state.hcaptcha.site_key" />
                        <x-input-error for="state.hcaptcha.site_key" class="mt-1 mb-2" />
                    </div>
                    <div class="mt-2">
                        <x-label for="hcaptcha_secret_key" value="{{ __('Secret Key') }}" />
                        <x-input id="hcaptcha_secret_key" type="text" class="mt-1 block w-full" wire:model="state.hcaptcha.secret_key" />
                        <x-input-error for="state.hcaptcha.secret_key" class="mt-1 mb-2" />
                    </div>
                </div>
            @endif
        </div>
        <div class="col-span-6">
            <x-label for="allowed_file_types" value="{{ __('Allowed File Types for Attachments') }}" new="true" />
            <x-textarea id="allowed_file_types" class="mt-1 block w-full resize-y" wire:model="state.allowed_file_types"></x-textarea>
            <x-input-error for="state.allow_reuse_email_in_days" class="mt-2" />
            <small>{{ __("File extensions separated by a common without any spaces") }}</small>
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
