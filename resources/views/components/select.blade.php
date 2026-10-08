@props(["disabled" => false, "size" => ""])

@php
    $sizeClasses = match ($size) {
        "xs" => "text-xs py-1 px-2.5",
        "sm" => "text-sm py-1.5 px-3",
        default => "text-sm py-2 px-3.5",
    };

    $attributes = $attributes->merge(["class" => "$sizeClasses"]);
@endphp

<select {{ $disabled ? "disabled" : "" }} {!! $attributes->merge(["class" => "border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-blue-600 dark:focus:border-blue-500 focus:ring-1 focus:ring-blue-600 dark:focus:ring-blue-500 outline-none rounded-lg shadow-xs transition-all duration-150"]) !!}>
    {{ $slot }}
</select>
