@props(["style" => "", "size" => "xs"])

@php
    $baseClasses = "inline-flex items-center px-4 py-2 border rounded-md font-semibold uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150";

    $styleClasses = match ($style) {
        "primary" => "bg-white text-indigo-600 border-indigo-600 hover:bg-indigo-50 focus:ring-indigo-500 dark:bg-transparent dark:text-indigo-400 dark:border-indigo-400 dark:hover:bg-indigo-900/10 dark:focus:ring-indigo-300",
        "success" => "bg-white text-green-600 border-green-600 hover:bg-green-50 focus:ring-green-500 dark:bg-transparent dark:text-green-400 dark:border-green-400 dark:hover:bg-green-900/10 dark:focus:ring-green-300",
        "error" => "bg-white text-red-600 border-red-600 hover:bg-red-50 focus:ring-red-500 dark:bg-transparent dark:text-red-400 dark:border-red-400 dark:hover:bg-red-900/10 dark:focus:ring-red-300",
        "warning" => "bg-white text-yellow-600 border-yellow-500 hover:bg-yellow-50 focus:ring-yellow-400 dark:bg-transparent dark:text-yellow-400 dark:border-yellow-400 dark:hover:bg-yellow-900/10 dark:focus:ring-yellow-200",
        "info" => "bg-white text-blue-600 border-blue-600 hover:bg-blue-50 focus:ring-blue-500 dark:bg-transparent dark:text-blue-400 dark:border-blue-400 dark:hover:bg-blue-900/10 dark:focus:ring-blue-300",
        default => "bg-white text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800",
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

<button {{ $attributes->merge(["type" => "button", "class" => "$baseClasses $styleClasses $sizeClasses"]) }}>
    {{ $slot }}
</button>
