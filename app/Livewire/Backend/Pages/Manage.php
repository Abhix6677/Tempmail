<?php

namespace App\Livewire\Backend\Pages;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Translation;
use App\Services\Util;
use Livewire\Component;
use Livewire\WithPagination;

class Manage extends Component {
    use WithPagination;

    public $page, $addPage, $updatePage;

    public $filters = [
        'search' => '',
        'status' => null,
    ];

    public function mount() {
        $this->clearPageObject();
        $this->addPage = false;
        $this->updatePage = false;
    }

    public function showPageForm() {
        $this->addPage = true;
        $this->dispatch('component-updated');
    }

    public function clearAddUpdate() {
        if (isset($this->page['lang'])) {
            unset($this->page['lang']);
            $this->showUpdate($this->page['id']);
        } else {
            $this->addPage = false;
            $this->updatePage = false;
            $this->clearPageObject();
        }
    }

    public function clearPageObject() {
        $this->resetErrorBag();
        $this->page = [
            'title' => '',
            'content' => '',
            'slug' => '',
            'meta' => [],
            'header' => null,
            'is_published' => false,
        ];
    }

    /**
     * Validation rules for the page form.
     */
    protected function rules() {
        return [
            'page.title' => 'required|string|max:255',
            'page.content' => 'required|string',
            'page.slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/',
            'page.meta.*.name' => 'required|string|max:255',
            'page.meta.*.content' => 'required|string',
        ];
    }

    /**
     * Custom validation messages for the page form.
     */
    protected function messages() {
        return [
            'page.title.required' => 'Page Title is required.',
            'page.content.required' => 'Please enter some content for the page.',
            'page.slug.required' => 'Page Slug is required.',
            'page.slug.regex' => 'Page Slug may only contain lowercase letters, numbers, and hyphens.',
            'page.meta.*.name.required' => 'Meta tag name is required.',
            'page.meta.*.content.required' => 'Meta tag content is required.',
        ];
    }

    /**
     * Save a page (add or update).
     */
    public function savePage() {
        $this->dispatch('component-updated');
        $this->validate($this->rules(), $this->messages());

        // Slug duplicate check for main page (not translations)
        if (!isset($this->page['lang'])) {
            $slugQuery = Page::where('slug', $this->page['slug']);
            if (isset($this->page['id'])) {
                $slugQuery->where('id', '!=', $this->page['id']); // Exclude current page for updates
            }
            if ($slugQuery->exists()) {
                $this->addError('page.slug', 'Page with this slug already exists.');
                return;
            }
        }

        // Handle meta
        $meta = $this->page['meta'];

        if (isset($this->page['lang'])) {
            // Save translation
            $this->saveTranslation($meta);
        } else {
            // Save main page
            $this->saveMainPage($meta);
        }

        // Restore meta for the form
        $this->page['meta'] = $meta;

        $this->dispatch('saved');
    }

    /**
     * Save the main page (not a translation).
     */
    private function saveMainPage($meta) {
        $page = isset($this->page['id']) ? Page::findOrFail($this->page['id']) : new Page;

        $pageData = $this->page;
        $pageData['meta'] = $meta;

        $page->fill($pageData);
        $page->save();

        // Create menu for new pages
        if (!isset($this->page['id'])) {
            $this->createMenu();
        }
    }

    /**
     * Save a translation for the page.
     */
    private function saveTranslation($meta) {
        $page = Page::findOrFail($this->page['id']);

        $translation = Translation::where('translatable_id', $page->id)
            ->where('translatable_type', 'page')
            ->where('language', $this->page['lang'])
            ->first();

        if (!$translation) {
            $translation = new Translation();
            $translation->translatable_id = $page->id;
            $translation->translatable_type = 'page';
            $translation->language = $this->page['lang'];
        }

        $translation->title = $this->page['title'];
        $translation->content = $this->page['content'];
        $translation->meta = $meta;
        $translation->header = $this->page['header'] ?? null;

        $translation->save();
    }

    /**
     * Show the update form for a page.
     */
    public function showUpdate($page_id) {
        $this->updatePage = true;
        $page = Page::findOrFail($page_id);
        $this->page = $page->toArray();
        $this->page['meta'] = $this->page['meta'] ?: [];
        $this->dispatch('component-updated');
    }

    /**
     * Translate a page to another language.
     */
    public function translate($language) {
        $page = Page::findOrFail($this->page['id']);
        $translation = $page->translation($language);

        if ($translation) {
            // Load existing translation
            $this->page['title'] = $translation->title;
            $this->page['content'] = $translation->content;
            $this->page['meta'] = $translation->meta ?: [];
            $this->page['header'] = $translation->header;
        } else {
            // Create new translation with default values
            $this->page['title'] = $page->title;
            $this->page['content'] = $page->content;
            $this->page['meta'] = $page->meta;
            $this->page['header'] = $page->header;
        }

        $this->page['lang'] = $language;
        $details = config('app.settings.languages')[$language];
        $this->page['lang_text'] = $details['label'];
        $this->dispatch('component-updated');
    }

    /**
     * Check if a translation exists for a given language.
     */
    public function isTranslated($language) {
        if (!isset($this->page['id'])) {
            return false;
        }
        $page = Page::findOrFail($this->page['id']);
        return $page->hasTranslation($language);
    }

    /**
     * Delete a page and its menu link.
     */
    public function delete($page_id) {
        // If multiple IDs are passed (array), handle each one
        if (is_array($page_id)) {
            foreach ($page_id as $id) {
                $page = Page::find($id);
                if (!$page) {
                    continue;
                }

                Util::deletePageMenuLink($page->id);
                $page->translations()->delete();
                $page->delete();
            }
            return;
        }

        $page = Page::findOrFail($page_id);
        Util::deletePageMenuLink($page->id);

        // Delete all translations for this page
        $page->translations()->delete();

        // Delete the main page
        $page->delete();
    }

    /**
     * Add a new meta tag row.
     */
    public function addMeta() {
        $this->page['meta'][] = [
            'name' => '',
            'content' => ''
        ];
        $this->dispatch('component-updated');
    }

    /**
     * Delete a meta tag row.
     */
    public function deleteMeta($key) {
        unset($this->page['meta'][$key]);
        $this->page['meta'] = array_values($this->page['meta']); // reindex
        $this->dispatch('component-updated');
    }

    public function clearFilters() {
        $this->filters = [
            'search' => '',
            'status' => null,
        ];
        $this->dispatch('component-updated');
    }

    /**
     * Render the page management view.
     */
    public function render() {
        if ($this->filters['status'] == '') {
            $this->filters['status'] = null; // Ensure status is null if empty
        }
        $pages = Page::query()
            ->when($this->filters['search'], function ($query) {
                $query->where('title', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('slug', 'like', '%' . $this->filters['search'] . '%');
            })
            ->when($this->filters['status'] !== null, function ($query) {
                $query->where('is_published', $this->filters['status']);
            })
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('backend.pages.manage', [
            'pages' => $pages,
        ]);
    }

    /**
     * Create a menu item for a newly created page.
     */
    private function createMenu() {
        $menu = new Menu;
        $menu->name = $this->page['title'];
        $menu->link = env('APP_URL') . '/' . $this->page['slug'];
        $menu->parent_id = null;
        $order = Menu::select('order')->where('parent_id', null)->orderBy('order', 'desc')->first();
        $menu->order = (($order) ? $order->order : 0) + 1;
        $menu->target = '_self';
        $menu->status = true;
        $menu->save();
    }
}
