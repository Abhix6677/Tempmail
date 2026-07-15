<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace for Livewire component classes in
    | your application. This value affects component auto-discovery and
    | any Livewire file helper commands.
    |
    */

    'class_namespace' => 'App\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path for Livewire component views. Livewire
    | will look for component views in this directory when rendering
    | components.
    |
    */

    'view_path' => resource_path('views/livewire'),

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | The default layout view that will be used by Livewire when rendering
    | components. Components can override this by setting the layout in
    | their render() method.
    |
    */

    'layout' => 'layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Livewire Assets URL
    |--------------------------------------------------------------------------
    |
    | This value sets the base URL for Livewire JavaScript assets. This
    | ensures the assets are served correctly when the application is
    | behind a CDN or proxy.
    |
    */

    'asset_url' => null,

    /*
    |--------------------------------------------------------------------------
    | App URL
    |--------------------------------------------------------------------------
    |
    | This value should be the same as APP_URL in your .env file. If
    | it's set to null, Livewire will try to detect the URL automatically.
    |
    */

    'app_url' => env('APP_URL'),

    /*
    |--------------------------------------------------------------------------
    | Turbo / Alpine
    |--------------------------------------------------------------------------
    |
    | These settings control Livewire's integration with Alpine.js.
    |
    */

    'turbo' => [
        'enabled' => env('LIVEWIRE_TURBO_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Serialization
    |--------------------------------------------------------------------------
    |
    | These settings control how Livewire serializes and deserializes
    | component data.
    |
    */

    'serialize' => [
        'max_depth' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backend Cache
    |--------------------------------------------------------------------------
    |
    | These settings control caching of components on the backend.
    |
    */

    'cache' => [
        'enabled' => env('LIVEWIRE_CACHE_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Inject Alpine Scripts
    |--------------------------------------------------------------------------
    |
    | Livewire automatically injects Alpine.js when it detects it's needed.
    | You can configure this behavior here.
    |
    */

    'inject_alpine' => env('LIVEWIRE_INJECT_ALPINE', true),

    /*
    |--------------------------------------------------------------------------
    | Inject Alpine Morph Plugin
    |--------------------------------------------------------------------------
    |
    | Livewire uses Alpine's morph plugin to efficiently update the DOM.
    | Configuring this ensures proper Alpine integration.
    |
    */

    'inject_morph' => env('LIVEWIRE_INJECT_MORPH', true),

    /*
    |--------------------------------------------------------------------------
    | Legacy Model Binding
    |--------------------------------------------------------------------------
    |
    | Enable or disable legacy model binding features.
    |
    */

    'legacy_model_binding' => env('LIVEWIRE_LEGACY_MODEL_BINDING', false),

    /*
    |--------------------------------------------------------------------------
    | Alpine JS CDN
    |--------------------------------------------------------------------------
    |
    | The CDN URL for Alpine.js when injected by Livewire.
    |
    */

    'alpine_cdn_url' => env('LIVEWIRE_ALPINE_CDN_URL', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js'),

];
