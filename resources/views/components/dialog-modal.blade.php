@props(["id" => null, "maxWidth" => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg font-medium text-gray-900 dark:text-gray-100 py-2">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 max-h-[60vh] overflow-y-auto scrollbar-thin scrollbar-thumb-transparent scrollbar-track-transparent px-px" style="scrollbar-width: none; -ms-overflow-style: none">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 dark:bg-gray-800 text-end">
        {{ $footer }}
    </div>
</x-modal>
