@extends('layouts.backend')

@section('content')
<div x-data="{ tab: 'general' }" class="space-y-6">

    <!-- Tabs Navigation -->
    <div class="flex flex-wrap gap-2 border-b pb-2">
        <button @click="tab='general'" :class="tab==='general' ? 'font-bold border-b-2 border-blue-600' : ''">General</button>
        <button @click="tab='engine'" :class="tab==='engine' ? 'font-bold border-b-2 border-blue-600' : ''">Engine</button>
        <button @click="tab='imap'" :class="tab==='imap' ? 'font-bold border-b-2 border-blue-600' : ''">IMAP</button>
        <button @click="tab='configuration'" :class="tab==='configuration' ? 'font-bold border-b-2 border-blue-600' : ''">Configuration</button>
        <button @click="tab='mail'" :class="tab==='mail' ? 'font-bold border-b-2 border-blue-600' : ''">Mail</button>
        <button @click="tab='socials'" :class="tab==='socials' ? 'font-bold border-b-2 border-blue-600' : ''">Socials</button>
        <button @click="tab='languages'" :class="tab==='languages' ? 'font-bold border-b-2 border-blue-600' : ''">Languages</button>
        <button @click="tab='advance'" :class="tab==='advance' ? 'font-bold border-b-2 border-blue-600' : ''">Advance</button>
        <button @click="tab='ads'" :class="tab==='ads' ? 'font-bold border-b-2 border-blue-600' : ''">Ads</button>
        <button @click="tab='export'" :class="tab==='export' ? 'font-bold border-b-2 border-blue-600' : ''">Export</button>
    </div>

    <!-- Lazy Loaded Components -->
    <div x-show="tab==='general'" x-cloak>
        @livewire('backend.settings.general')
    </div>

    <div x-show="tab==='engine'" x-cloak>
        @livewire('backend.settings.engine')
    </div>

    <div x-show="tab==='imap'" x-cloak>
        @livewire('backend.settings.imap')
    </div>

    <div x-show="tab==='configuration'" x-cloak>
        @livewire('backend.settings.configuration')
    </div>

    <div x-show="tab==='mail'" x-cloak>
        @livewire('backend.settings.mail')
    </div>

    <div x-show="tab==='socials'" x-cloak>
        @livewire('backend.settings.socials')
    </div>

    <div x-show="tab==='languages'" x-cloak>
        @livewire('backend.settings.languages')
    </div>

    <div x-show="tab==='advance'" x-cloak>
        @livewire('backend.settings.advance')
    </div>

    <div x-show="tab==='ads'" x-cloak>
        @livewire('backend.settings.ads')
    </div>

    <div x-show="tab==='export'" x-cloak>
        @livewire('backend.settings.exportimport')
    </div>

</div>
@endsection
