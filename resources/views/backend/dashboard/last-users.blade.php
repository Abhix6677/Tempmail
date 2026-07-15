<div class="h-full">
    <x-card class="h-full">
        <div class="flex justify-between items-center border-b px-6 py-4 text-sm dark:border-gray-700">
            {{ __("Lastest Users ") }}
            <a href="{{ route("users") }}">
                <x-button type="button">{{ __("View All") }}</x-button>
            </a>
        </div>
        <div class="max-h-[350px] overflow-y-auto scrollbar-thin scrollbar-thumb-transparent scrollbar-track-transparent">
            @foreach ($users as $user)
                <div class="flex items-center gap-4 px-6 py-3">
                    <div class="flex-shrink-0">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</span>
                    </div>
                    <div class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        {{ $user->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
</div>
