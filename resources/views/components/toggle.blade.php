@props(["disabled" => false, "id" => "id"])

<div class="relative">
    <input {{ $disabled ? "disabled" : "" }} id="{{ $id }}" type="checkbox" {!! $attributes->merge(["class" => "hidden"]) !!} />
    <div class="toggle-path bg-gray-200 dark:bg-gray-600 w-9 h-5 rounded-full shadow-inner"></div>
    <div class="toggle-circle absolute w-3.5 h-3.5 bg-white rounded-full shadow inset-y-0 left-0"></div>
</div>
