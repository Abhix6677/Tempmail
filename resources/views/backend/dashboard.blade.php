@section("title", "Dashboard")

@extends('layouts.backend')

@section('header')
    <h2 class="font-semibold text-xl leading-tight">
        {{ __("Dashboard") }}
    </h2>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-10 px-3 sm:px-6 lg:px-8 flex flex-col gap-6">
        @php
            $cronLog = \App\Models\CronLog::latest()->first();
            $cronMessage = null;
            $cronMessageType = "error";
            if ($cronLog && $cronLog->created_at < now()->subDay()) {
                $cronMessage = "Your CRON is not running for more than 24 hours.";
            } elseif ($cronLog == null) {
                $cronMessageType = "info";
                $cronMessage = "Please setup CRON which is required for deleting older emails.";
            }
        @endphp

        @if ($cronMessage)
            {{-- Basic success alert --}}
            <x-alert type="{{ $cronMessageType }}" title="{{ $cronMessage }}">
                <p class="mt-5">{{ __("Setup below CRON command for every minute") }}</p>
                <span class="inline-block mt-2 p-4 bg-black/10 text-black dark:text-white rounded-xl">wget -O /dev/null -o /dev/null {{ config("app.url") }}/api/cron/{{ config("app.settings.cron_password") }}</span>
                <br />
                <a href="https://helpdesk.thehp.in/hc/articles/1/19/3/cron-setup" target="_blank">
                    <x-button class="mt-3" size="xs">{{ __("More Info") }}</x-button>
                </a>
            </x-alert>
        @endif

        @livewire("backend.dashboard.stats")
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>@livewire("backend.dashboard.messages-received")</div>
            <div>@livewire("backend.dashboard.emails-created")</div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="col-span-1 lg:col-span-2">@livewire("backend.dashboard.users-registered")</div>
            <div>@livewire("backend.dashboard.last-users")</div>
        </div>
    </div>
@endsection
