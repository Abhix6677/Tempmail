@props(["style" => "", "size" => "xs", "icon" => "hgi-arrow-right-01", "position" => "right"])

@php
    $baseClasses = "flex gap-1 items-center px-4 py-2 border border-transparent rounded-md font-semibold uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150";

    $styleClasses = match ($style) {
        "primary" => "bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500 dark:bg-indigo-400 dark:text-gray-900 dark:hover:bg-indigo-300 dark:focus:ring-indigo-300",
        "success" => "bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 dark:bg-green-400 dark:text-gray-900 dark:hover:bg-green-300 dark:focus:ring-green-300",
        "error" => "bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-400 dark:text-gray-900 dark:hover:bg-red-300 dark:focus:ring-red-300",
        "warning" => "bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-400 dark:bg-yellow-300 dark:text-gray-900 dark:hover:bg-yellow-200 dark:focus:ring-yellow-200",
        "info" => "bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 dark:bg-blue-400 dark:text-gray-900 dark:hover:bg-blue-300 dark:focus:ring-blue-300",
        default => "bg-gray-800 text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:ring-indigo-500 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:active:bg-gray-300 dark:focus:ring-offset-gray-800",
    };

    $sizeClasses = match ($size) {
        "xs" => "text-xs",
        "sm" => "text-sm",
        "md" => "text-base",
        "lg" => "text-lg",
        "xl" => "text-xl",
        default => "",
    };
@endphp

<button {{ $attributes->merge(["type" => "submit", "class" => "$baseClasses $styleClasses $sizeClasses"]) }}>
    @if ($position == "left")
        <i class="hgi hgi-stroke {{ $icon }}"></i>
    @endif

    {{ $slot }}
    @if ($position == "right")
        <i class="hgi hgi-stroke {{ $icon }}"></i>
    @endif
</button>
