@props(["disabled" => false, "prefix" => ""])

<div class="flex items-center mt-1 w-full border overflow-hidden border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus-within:outline-2 focus-within:outline-offset-2 focus-within:ring-1 focus-within:border-indigo-500 dark:focus-within:border-indigo-600 focus-within:ring-indigo-500 dark:focus-within:ring-indigo-600 rounded-md shadow-sm">
    <div class="ps-3 py-2 text-gray-500">{{ $prefix }}</div>
    <input {{ $disabled ? "disabled" : "" }} {!! $attributes->merge(["class" => "ps-0 bg-transparent flex-1 border-0 focus:outline-0 focus:shadow-none focus:ring-0"]) !!} />
</div>
