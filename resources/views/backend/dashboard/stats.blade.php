<div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-6">
        <x-card class="px-6 py-4 col-span-2 md:col-span-4 lg:col-span-2">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-mail-receive-02"></i></div>
            <div class="ml-1 text-2xl font-bold">{{ $messagesReceived }}</div>
            <div class="ml-1 text-sm text-gray-500">{{ __("Messages Received") }}</div>
        </x-card>
        <x-card class="px-6 py-4 col-span-1 lg:col-span-2">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-mail-at-sign-01"></i></div>
            <div class="ml-1 text-2xl font-bold">{{ $emailsCreated }}</div>
            <div class="ml-1 text-sm text-gray-500">{{ __("Emails Generated") }}</div>
        </x-card>
        <x-card class="px-6 py-4">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-license"></i></div>
            <div class="ml-1 text-2xl font-bold">{{ $pagesCreated }}</div>
            <div class="ml-1 text-sm text-gray-500">{{ __("Pages") }}</div>
        </x-card>
        <x-card class="px-6 py-4">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-pencil-edit-02"></i></div>
            <div class="ml-1 text-2xl font-bold">{{ $blogPostsCreated }}</div>
            <div class="ml-1 text-sm text-gray-500">{{ __("Post") }}</div>
        </x-card>
        <x-card class="px-6 py-4">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-user-multiple-02"></i></div>
            <div class="ml-1 text-2xl font-bold">{{ $usersRegistered }}</div>
            <div class="ml-1 text-sm text-gray-500">{{ __("Users") }}</div>
        </x-card>
    </div>
</div>
