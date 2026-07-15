<main class="py-10 px-3 sm:px-6 lg:px-8">
    @if (Laravel\Fortify\Features::canUpdateProfileInformation())
        @livewire("profile.update-profile-information-form")
        <div class="my-10"></div>
    @endif

    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
        <div class="mt-10 sm:mt-0">
            @livewire("profile.update-password-form")
        </div>
        <div class="my-10"></div>
    @endif

    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
        <div class="mt-10 sm:mt-0">
            @livewire("profile.two-factor-authentication-form")
        </div>
        <div class="my-10"></div>
    @endif

    <div class="mt-10 sm:mt-0">
        @livewire("profile.logout-other-browser-sessions-form")
    </div>

    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
        <div class="my-10"></div>
        <div class="mt-10 sm:mt-0">
            @livewire("profile.delete-user-form")
        </div>
    @endif
</main>
