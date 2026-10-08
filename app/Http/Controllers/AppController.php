<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Services\TMail;
use App\Services\Util;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class AppController extends Controller {
    /**
     * Unlock the mailbox with password.
     */
    public function unlock(Request $request) {
        $password = $request->password;
        if ($password !== config('app.settings.lock.password')) {
            Session::flash('error', __('Invalid Password'));
        }
        session(['password' => $password]);
        return redirect()->back();
    }

    /**
     * Load homepage or mailbox.
     */
    public function load() {
        $this->checkLinking();
        $homepage = config('app.settings.homepage');

        if ($homepage == 0) {
            if (config('app.settings.disable_mailbox_slug')) {
                return $this->app();
            }
            TMail::getEmail(true);
            return redirect(Util::localizeRoute('mailbox'));
        }

        $page = Util::getTranslatedPage($homepage);
        if (!$page) {
            abort(404);
        }
        $page = $this->setHeaders($page);
        $theme = config('app.settings.theme') ?: 'default';
        if (!view()->exists("frontend.themes.$theme.app")) {
            $theme = 'default';
        }
        return view("frontend.themes.$theme.app", compact('page'));
    }

    /**
     * Show mailbox or redirect based on config.
     */
    public function mailbox($email = null) {
        // Redirect loop safeguard: track in session how many times /mailbox was
        // hit consecutively without an email being set. If it exceeds 3, render
        // the mailbox directly to break the loop.
        $redirectCount = (int) session('_mailbox_redirect_count', 0);
        if (!TMail::getEmail()) {
            // No email — count up
            session(['_mailbox_redirect_count' => $redirectCount + 1]);
        } else {
            // Email exists — reset the counter
            session(['_mailbox_redirect_count' => 0]);
        }

        // If we've cycled too many times, force-render the mailbox to break the loop
        if ($redirectCount >= 3) {
            session(['_mailbox_redirect_count' => 0]);
            // Ensure an email exists before rendering
            if (!TMail::getEmail()) {
                TMail::getEmail(true);
            }
            return $this->app();
        }

        if ($email && config('app.settings.enable_create_from_url')) {
            TMail::createCustomEmailFull($email);
            return redirect(Util::localizeRoute('mailbox'));
        }

        if (config('app.settings.homepage') && !TMail::getEmail()) {
            return redirect(Util::localizeRoute('home'));
        }

        if (config('app.settings.disable_mailbox_slug')) {
            return redirect(Util::localizeRoute('home'));
        }

        return $this->app();
    }

    /**
     * View message or redirect to mailbox.
     */
    public function message($messageId) {
        if (config('app.settings.disable_mailbox_slug')) {
            return redirect(Util::localizeRoute('home'));
        }
        return redirect(Util::localizeRoute('mailbox'));
    }

    /**
     * Render the main app view.
     */
    public function app() {
        $theme = config('app.settings.theme') ?: 'default';

        if (!view()->exists("frontend.themes.$theme.app")) {
            $theme = 'default';
        }

        if ($theme === 'groot' && config('app.settings.theme_options.mailbox_page')) {
            $in_page = Util::getTranslatedPage(config('app.settings.theme_options.mailbox_page'));
            if ($in_page) {
                return view("frontend.themes.$theme.app", compact('in_page'));
            }
        }

        return view("frontend.themes.$theme.app");
    }

    /**
     * Show a page by slug.
     */
    public function page($slug = '') {
        $currentLocale = app()->getLocale();
        $defaultLocale = config('app.settings.language');

        // Get the main page
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$page) {
            abort(404);
        }

        // If we're not on the default language, try to get the translation
        if ($currentLocale !== $defaultLocale) {
            $translation = $page->translation($currentLocale);
            if ($translation) {
                // Create a temporary object with translated content
                $translatedPage = clone $page;
                $translatedPage->title = $translation->title;
                $translatedPage->content = $translation->content;
                $translatedPage->meta = $translation->meta;
                $translatedPage->header = $translation->header;
                $page = $translatedPage;
            }
        }

        $page = $this->setHeaders($page);
        if ($page->id === config('app.settings.homepage')) {
            return redirect(Util::localizeRoute('home'));
        }
        $theme = config('app.settings.theme') ?: 'default';
        if (!view()->exists("frontend.themes.$theme.app")) {
            $theme = 'default';
        }
        return view("frontend.themes.$theme.app", compact('page'));
    }

    /**
     * Show a blog post by slug.
     */
    public function blog($slug = '') {
        $currentLocale = app()->getLocale();
        $defaultLocale = config('app.settings.language');

        // Get the main post
        $post = Post::where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$post) {
            abort(404);
        }

        // If we're not on the default language, try to get the translation
        if ($currentLocale !== $defaultLocale) {
            $translation = $post->translation($currentLocale);
            if ($translation) {
                // Create a temporary object with translated content
                $translatedPost = clone $post;
                $translatedPost->title = $translation->title;
                $translatedPost->content = $translation->content;
                $translatedPost->meta = $translation->meta;
                $translatedPost->header = $translation->header;
                $post = $translatedPost;
            }
        }

        $post = $this->setHeaders($post);
        $theme = config('app.settings.theme') ?: 'default';
        if (!view()->exists("frontend.themes.$theme.app")) {
            $theme = 'default';
        }
        return view("frontend.themes.$theme.app", compact('post'));
    }

    /**
     * Show category posts.
     */
    public function category($slug) {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            abort(404);
        }

        $posts = Util::getBlogs($category->id);

        $theme = config('app.settings.theme') ?: 'default';
        if (!view()->exists("frontend.themes.$theme.app")) {
            $theme = 'default';
        }
        return view("frontend.themes.$theme.app", compact('category', 'posts'));
    }

    /**
     * Switch mailbox email.
     */
    public function switch($email) {
        TMail::setEmail($email);

        if (config('app.settings.disable_mailbox_slug')) {
            return redirect(Util::localizeRoute('home'));
        }

        return redirect(Util::localizeRoute('mailbox'));
    }

    /**
     * Show user profile.
     */
    public function profile() {
        if (!Auth::check()) {
            return redirect(Util::localizeRoute('home'));
        }
        $profile = true;
        $theme = config('app.settings.theme') ?: 'default';
        if (!view()->exists("frontend.themes.$theme.app")) {
            $theme = 'default';
        }
        return view("frontend.themes.$theme.app", compact('profile'));
    }

    /**
     * Set locale for the session.
     */
    public function locale(Request $request) {
        $currentLocale = app()->getLocale();
        $newLocale = $request->input('locale', $currentLocale);
        if ($newLocale !== $currentLocale) {
            $url = str_replace('/' . $currentLocale, '/' . $newLocale, url()->previous());
            app()->setLocale($newLocale);
            session(['locale' => $newLocale]);
            return redirect($url);
        }
        return redirect()->back();
    }

    /**
     * Sitemap Generator
     * @since 2.4.0
     */
    public function sitemap() {
        $pages = Page::select('id', 'slug', 'updated_at')
            ->where('is_published', true)
            ->get();

        $posts = Post::select('id', 'slug', 'updated_at')
            ->where('is_published', true)
            ->get();

        $contents = view('frontend.common.sitemap', compact('pages', 'posts'));
        return response($contents)->header('Content-Type', 'application/xml');
    }

    /**
     * Ensure symlinks for themes and storage exist.
     */
    private function checkLinking() {
        if (!file_exists(public_path('storage'))) {
            File::copyDirectory(storage_path('app/public'), public_path('storage'));
        }
        if (!file_exists(public_path('themes'))) {
            File::copyDirectory(resource_path('views/frontend/themes'), public_path('themes'));
        }
    }

    /**
     * Set meta and header tags for a page and post.
     */
    private function setHeaders($object) {
        if (!$object) {
            return $object;
        }

        $header = $object->header ?? '';
        $meta = $object->meta ?? [];

        // Handle both array and serialized string formats for backward compatibility
        if (is_string($meta)) {
            $meta = unserialize($meta) ?: [];
        }

        foreach ($meta as $metaItem) {
            if (!isset($metaItem['name']) || !isset($metaItem['content'])) {
                continue;
            }

            if ($metaItem['name'] === 'canonical') {
                $header .= '<link rel="canonical" href="' . e($metaItem['content']) . '" />';
            } elseif (str_contains($metaItem['name'], 'og:')) {
                $header .= '<meta property="' . e($metaItem['name']) . '" content="' . e($metaItem['content']) . '" />';
            } else {
                $header .= '<meta name="' . e($metaItem['name']) . '" content="' . e($metaItem['content']) . '" />';
            }
        }
        $object->header = $header;
        return $object;
    }

    /**
     * Proxy external images to bypass CORP / CORS restrictions and privacy blockers.
     */
    public function imageProxy(Request $request) {
        $url = $request->query('url');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
            abort(400);
        }

        $cacheKey = 'img_proxy_' . md5($url);
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$cached || !is_array($cached)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($data === false || $httpCode < 200 || $httpCode >= 400) {
                abort(404);
            }

            $contentType = $contentType ?: 'image/png';
            $cached = [
                'content_type' => $contentType,
                'data' => base64_encode($data)
            ];

            \Illuminate\Support\Facades\Cache::put($cacheKey, $cached, 86400);
        }

        $contentType = $cached['content_type'] ?? 'image/png';
        $data = base64_decode($cached['data'] ?? '');

        return response($data, 200, [
            'Content-Type' => $contentType,
            'Access-Control-Allow-Origin' => '*',
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
        ]);
    }
}
