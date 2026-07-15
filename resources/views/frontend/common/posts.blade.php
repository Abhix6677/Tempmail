<div class="flex flex-col gap-6 my-5">
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
        @foreach ($posts as $post)
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">
                <article class="relative">
                    <img class="rounded-t-lg h-60 w-full object-cover" src="{{ $post->image }}" alt="featured_image" />
                    <div class="absolute top-5 left-5">
                        @for ($i = 0; $i < min(3, count($post->categories)); $i++)
                            <a href="{{ Util::localizeUrl(route("blog.category", $post->categories[$i]->slug)) }}" class="inline bg-black text-white px-3 py-1 rounded-full text-sm">{{ __($post->categories[$i]->name) }}</a>
                        @endfor
                    </div>
                    <a class="block p-4 xl:p-6 2xl:p-8 space-y-2" href="{{ Util::localizeUrl(route("blog.post", $post->slug)) }}">
                        <h4 class="font-semibold text-lg m-0">{{ $post->title }}</h4>
                        <p class="text-sm text-gray-600">{{ $post->excerpt }}</p>
                    </a>
                </article>
            </div>
        @endforeach
    </section>

    {{ $posts->links() }}
</div>
