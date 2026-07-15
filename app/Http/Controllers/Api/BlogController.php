<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller {
    public function getBlogs(Request $request) {
        $lang = $request->input('lang', 'en');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 6);
        $search = $request->input('search', '');
        $order = $request->input('order', 'desc');
        $orderBy = $request->input('orderby', 'created_at');

        $posts = Post::with('categories', 'translations')
            ->where('title', 'like', "%{$search}%")
            ->where('is_published', true)
            ->orderBy($orderBy, $order)
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        // Apply translations if requested language is not the default
        $defaultLocale = config('app.settings.language');
        if ($lang !== $defaultLocale) {
            $posts->transform(function ($post) use ($lang) {
                $translation = $post->translation($lang);
                if ($translation) {
                    $post->title = $translation->title;
                    $post->content = $translation->content;
                    $post->meta = $translation->meta;
                    $post->header = $translation->header;
                }
                return $post;
            });
        }

        return response()->json($posts);
    }
}
