@section("title", "Pages")

@extends('layouts.backend')

@section('header')
    <h2 class="font-semibold text-xl leading-tight">
        {{ __("Pages") }}
    </h2>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-10 px-3 sm:px-6 lg:px-8">
        @livewire("backend.pages.manage")
    </div>
@endsection
