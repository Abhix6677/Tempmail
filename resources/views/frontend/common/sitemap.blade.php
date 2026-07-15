<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @if (config("app.settings.language_in_url"))
        @foreach (config("app.settings.languages") as $code => $language)
            @if ($language["is_active"])
                <url>
                    <loc>{{ config("app.url") . "/" . $code }}</loc>
                    <lastmod>{{ date(DATE_ATOM) }}</lastmod>
                    <priority>1.0</priority>
                </url>
            @endif
        @endforeach

        @foreach ($pages as $page)
            <url>
                @if ($page->lang)
                    <loc>{{ config("app.url") . "/" . $page->lang . "/" . $page->slug }}</loc>
                @else
                    <loc>{{ config("app.url") . "/" . config("app.settings.language") . "/" . $page->slug }}</loc>
                @endif
                <lastmod>{{ $page->updated_at->format("Y-m-d\TH:i:s.uP") }}</lastmod>
                <priority>0.9</priority>
            </url>
        @endforeach

        @foreach ($posts as $post)
            <url>
                @if ($post->lang)
                    <loc>{{ config("app.url") . "/" . $post->lang . "/blog/" . $post->slug }}</loc>
                @else
                    <loc>{{ config("app.url") . "/" . config("app.settings.language") . "/blog/" . $post->slug }}</loc>
                @endif
                <lastmod>{{ $post->updated_at->format("Y-m-d\TH:i:s.uP") }}</lastmod>
                <priority>0.8</priority>
            </url>
        @endforeach
    @else
        <url>
            <loc>{{ route("home") }}</loc>
            <lastmod>{{ date(DATE_ATOM) }}</lastmod>
            <priority>1.0</priority>
        </url>
        @foreach ($pages as $page)
            <url>
                <loc>{{ route("page", $page->slug) }}</loc>
                <lastmod>{{ $page->updated_at->format("Y-m-d\TH:i:s.uP") }}</lastmod>
                <priority>0.9</priority>
            </url>
        @endforeach

        @foreach ($posts as $post)
            <url>
                <loc>{{ route("page", $post->slug) }}</loc>
                <lastmod>{{ $post->updated_at->format("Y-m-d\TH:i:s.uP") }}</lastmod>
                <priority>0.8</priority>
            </url>
        @endforeach
    @endif
</urlset>
