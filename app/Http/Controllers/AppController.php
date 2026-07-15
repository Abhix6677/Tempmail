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
        $symlinks = [
            'themes' => public_path('themes'),
            'storage' => public_path('storage'),
        ];

        $needsLinking = false;

        foreach ($symlinks as $path) {
            if (!file_exists($path) || !is_link($path)) {
                if (file_exists($path)) {
                    is_dir($path) ? File::deleteDirectory($path) : File::delete($path);
                }
                $needsLinking = true;
            }
        }

        if ($needsLinking) {
            Artisan::call('storage:link');
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
}
