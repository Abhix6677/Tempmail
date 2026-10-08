@props(["disabled" => false])

<textarea {{ $disabled ? "disabled" : "" }} {!! $attributes->merge(["class" => "border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-blue-600 dark:focus:border-blue-500 focus:ring-1 focus:ring-blue-600 dark:focus:ring-blue-500 outline-none rounded-lg shadow-xs p-3 transition-all duration-150"]) !!}></textarea>
