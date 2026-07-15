@section("title", __("TMail - Installer"))

<x-guest-layout>
    <main class="py-12">
        <div x-data="{
            darkmode: localStorage.getItem('darkmode') == 'enabled' ? true : false,
        }" class="absolute top-2 right-3 text-xl cursor-pointer mt-2">
            <i onclick="enableDarkMode()" x-on:click="darkmode = true" x-show="!darkmode" class="hgi hgi-stroke hgi-moon-02"></i>
            <i onclick="disableDarkMode()" x-on:click="darkmode = false" x-show="darkmode" class="hgi hgi-stroke hgi-sun-03 text-yellow-500"></i>
        </div>
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 overflow-hidden rounded-2xl">
                <div class="pt-12">
                    <img class="m-auto max-w-48 dark:hidden" src="{{ asset("images/installer-logo-light.png") }}" alt="logo" />
                    <img class="m-auto max-w-48 hidden dark:block" src="{{ asset("images/installer-logo-dark.png") }}" alt="logo" />
                </div>
                <div class="p-10">
                    @livewire("installer.installer")
                </div>
            </div>
        </div>
    </main>
</x-guest-layout>
