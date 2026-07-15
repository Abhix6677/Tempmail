@props([
    "submit",
    "view" => "column",
])

@php
    $baseClasses = "";
    $viewClasses = match ($view) {
        "full" => "flex flex-col gap-5",
        default => "md:grid md:grid-cols-3 md:gap-6",
    };
@endphp

<div {{ $attributes->merge(["class" => "$baseClasses $viewClasses"]) }}>
    @if (isset($title) && isset($description))
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>
    @endif

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow {{ isset($actions) ? "sm:rounded-tl-2xl sm:rounded-tr-2xl" : "sm:rounded-2xl" }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-2xl sm:rounded-br-2xl">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
