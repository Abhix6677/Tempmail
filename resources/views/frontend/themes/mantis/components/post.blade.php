<main class="post flex-1 p-5">
    <div class="relative h-96 bg-cover bg-center bg-no-repeat rounded-lg overflow-hidden" style="background-image: url('{{ $post->image }}')">
        <!-- Dark overlay for better text readability -->
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <!-- Content overlay -->
        <div class="relative z-10 flex flex-col justify-end h-full p-6 md:p-8 lg:p-10">
            <!-- Categories -->
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($post->categories as $category)
                    <a href="{{ Util::localizeUrl(route("blog.category", $category->slug)) }}" class="px-3 py-1 bg-gray-900 dark:bg-gray-700 text-white dark:text-gray-300 text-sm font-medium rounded-full">{{ $category->name }}</a>
                @endforeach
            </div>

            <!-- Title -->
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-white dark:text-gray-200 mb-4 max-w-4xl leading-tight">{{ $post->title }}</h1>

            <!-- Date -->
            <p class="text-gray-200 dark:text-gray-400 font-medium">{{ $post->created_at->format("F j, Y") }}</p>
        </div>
    </div>

    <!-- Blog Content -->
    <article class="py-6 md:py-8 lg:py-10 text-gray-800 dark:text-gray-300">
        {!! $post->content !!}
    </article>

    @if (config("app.settings.disqus.enable"))
        <div id="disqus_thread"></div>
        <script>
            (function () {
                // DON'T EDIT BELOW THIS LINE
                var d = document,
                    s = d.createElement('script');
                s.src = 'https://{{ config("app.settings.disqus.shortname") }}.disqus.com/embed.js';
                s.setAttribute('data-timestamp', +new Date());
                (d.head || d.body).appendChild(s);
            })();
        </script>
    @endif
</main>
