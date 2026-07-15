<a href="{{ Util::localizeRoute("home") }}">
    @if (config("app.settings.logo") && Illuminate\Support\Facades\Storage::disk("local")->has(config("app.settings.logo")))
        <img class="w-56" src="{{ url("storage/" . config("app.settings.logo")) }}" alt="logo" />
    @elseif (Illuminate\Support\Facades\Storage::disk("local")->has("public/images/custom-logo.png"))
        <img class="max-w-56" src="{{ url("storage/images/custom-logo.png") }}" alt="logo" />
    @else
        <img class="max-w-56" src="{{ asset("images/logo.png") }}" alt="logo" />
    @endif
</a>
