<?php

namespace App\Livewire\Backend\Blog;

use App\Models\Category;
use App\Models\Post;
use App\Models\Translation;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Component;

class Manage extends Component {

    use WithFileUploads, WithPagination;

    public $post, $addPost, $updatePost, $image, $categories, $showCategoryModal = false;

    public $categoryForm = [
        'id' => null,
        'name' => '',
        'slug' => '',
    ];

    public $filters = [
        'search' => '',
        'category' => null,
        'status' => null,
    ];

    /**
     * Validation rules for the blog post form.
     */
    protected function rules() {
        return [
            'post.title' => 'required|string|max:255',
            'post.content' => 'required|string',
            'post.slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/',
            'post.meta.*.name' => 'required|string|max:255',
            'post.meta.*.content' => 'required|string',
            'post.categories' => 'nullable|array',
            'post.categories.*' => 'integer|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    /**
     * Custom validation messages for the blog post form.
     */
    protected function messages() {
        return [
            'post.title.required' => 'Post Title is required.',
            'post.content.required' => 'Please enter some content for the post.',
            'post.slug.required' => 'Post Slug is required.',
            'post.slug.regex' => 'Post Slug may only contain lowercase letters, numbers, and hyphens.',
            'post.meta.*.name.required' => 'Meta tag name is required.',
            'post.meta.*.content.required' => 'Meta tag content is required.',
            'post.categories.*.exists' => 'Selected category does not exist.',
            'image.image' => 'The feature image must be an image file.',
            'image.mimes' => 'The feature image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The feature image must not be greater than 2MB.',
        ];
    }

    /**
     * Validation rules for the category form.
     */
    protected function categoryRules() {
        return [
            'categoryForm.name' => 'required|string|max:255',
            'categoryForm.slug' => 'required|string|max:255|unique:categories,slug,' . ($this->categoryForm['id'] ?? 'NULL') . ',id',
        ];
    }

    /**
     * Custom validation messages for the category form.
     */
    protected function categoryMessages() {
        return [
            'categoryForm.name.required' => 'Category name is required',
            'categoryForm.slug.required' => 'Category slug is required',
            'categoryForm.slug.unique' => 'Category slug must be unique',
        ];
    }

    /**
     * Initialize component state.
     */
    public function mount() {
        $this->clearPostObject();
        $this->addPost = false;
        $this->updatePost = false;
        $this->categories = Category::get();
    }

    /**
     * Show the form to add a new post.
     */
    public function showPostForm() {
        $this->addPost = true;
        $this->dispatch('component-updated');
    }

    public function clearImage() {
        $this->image = null;
        $this->post['image'] = '';
    }

    /**
     * Clear the add/update form.
     */
    public function clearAddUpdate() {
        $this->resetValidation();
        if (isset($this->post['lang'])) {
            unset($this->post['lang']);
            $this->showUpdate($this->post['id']);
        } else {
            $this->addPost = false;
            $this->updatePost = false;
            $this->clearPostObject();
        }
    }

    /**
     * Clear the post object.
     */
    public function clearPostObject() {
        $this->resetErrorBag();
        $this->post = [
            'title' => '',
            'content' => '',
            'excerpt' => '',
            'slug' => '',
            'image' => '',
            'meta' => [],
            'header' => null,
            'categories' => [],
            'is_published' => false,
        ];
        $this->image = null;
    }

    /**
     * Handle the image upload for the post.
     */
    protected function handleImageUpload() {
        if ($this->image) {
            $image = $this->image->storeAs('images', $this->image->getClientOriginalName(), 'public');
            $this->post['image'] = asset('storage/' . $image);
        }
    }

    /**
     * Save a blog post (add or update).
     */
    public function savePost() {
        $this->dispatch('component-updated');
        $this->validate($this->rules(), $this->messages());

        // Slug duplicate check for main post (not translations)
        if (!isset($this->post['lang'])) {
            $slugQuery = Post::where('slug', $this->post['slug']);
            if (isset($this->post['id'])) {
                $slugQuery->where('id', '!=', $this->post['id']); // Exclude current post for updates
            }
            if ($slugQuery->exists()) {
                $this->addError('post.slug', 'Post with this slug already exists.');
                return;
            }
        }

        $this->handleImageUpload();

        // Handle meta and categories
        $meta = $this->post['meta'];
        $categories = $this->post['categories'] ?? [];

        if (isset($this->post['lang'])) {
            // Save translation
            $this->saveTranslation($meta);
        } else {
            // Save main post
            $this->saveMainPost($meta, $categories);
        }

        // Restore meta for the form
        $this->post['meta'] = $meta;

        $this->dispatch('saved');
    }

    /**
     * Save the main post (not a translation).
     */
    private function saveMainPost($meta, $categories) {
        $post = isset($this->post['id']) ? Post::findOrFail($this->post['id']) : new Post;

        $postData = $this->post;
        $postData['meta'] = $meta;

        $post->fill($postData);
        $post->save();
        $post->categories()->sync($categories);
    }

    /**
     * Save a translation for the post.
     */
    private function saveTranslation($meta) {
        $post = Post::findOrFail($this->post['id']);

        $translation = Translation::where('translatable_id', $post->id)
            ->where('translatable_type', 'post')
            ->where('language', $this->post['lang'])
            ->first();

        if (!$translation) {
            $translation = new Translation();
            $translation->translatable_id = $post->id;
            $translation->translatable_type = 'post';
            $translation->language = $this->post['lang'];
        }

        $translation->title = $this->post['title'];
        $translation->content = $this->post['content'];
        $translation->meta = $meta;
        $translation->header = $this->post['header'] ?? null;

        $translation->save();
    }

    /**
     * Show the update form for a blog post.
     */
    public function showUpdate($post_id) {
        $this->updatePost = true;
        $post = Post::findOrFail($post_id);
        $this->post = $post->toArray();
        $this->post['meta'] = $this->post['meta'] ?: [];
        $this->post['categories'] = $post->categories()->pluck('id')->toArray();
        $this->dispatch('component-updated');
    }

    /**
     * Translate the post to a different language.
     */
    public function translate($language) {
        $post = Post::findOrFail($this->post['id']);
        $translation = $post->translation($language);

        if ($translation) {
            // Load existing translation
            $this->post['title'] = $translation->title;
            $this->post['content'] = $translation->content;
            $this->post['meta'] = $translation->meta ?: [];
            $this->post['header'] = $translation->header;
        } else {
            // Create new translation with default values
            $this->post['title'] = $post->title;
            $this->post['content'] = $post->content;
            $this->post['meta'] = $post->meta;
            $this->post['header'] = $post->header;
        }

        $this->post['lang'] = $language;
        $details = config('app.settings.languages')[$language];
        $this->post['lang_text'] = $details['label'];
        $this->dispatch('component-updated');
    }

    /**
     * Check if the post is translated to a specific language.
     */
    public function isTranslated($language) {
        if (!isset($this->post['id'])) {
            return false;
        }
        $post = Post::findOrFail($this->post['id']);
        return $post->hasTranslation($language);
    }

    /**
     * Delete a post.
     */
    public function delete($post_id) {
        $post = Post::findOrFail($post_id);

        // Delete all translations for this post
        $post->translations()->delete();

        // Delete the main post
        $post->delete();
    }

    /**
     * Add a new meta tag row.
     */
    public function addMeta() {
        $this->post['meta'][] = [
            'name' => '',
            'content' => ''
        ];
        $this->dispatch('component-updated');
    }

    /**
     * Delete a meta tag row.
     */
    public function deleteMeta($key) {
        unset($this->post['meta'][$key]);
        $this->post['meta'] = array_values($this->post['meta']); // reindex
        $this->dispatch('component-updated');
    }

    /**
     * Add a new category.
     */
    public function addCategory() {
        $this->validate($this->categoryRules(), $this->categoryMessages());
        // Check for duplicate slug
        if (Category::where('slug', $this->categoryForm['slug'])->exists()) {
            $this->addError('categoryForm.slug', 'Category slug must be unique');
            return;
        }
        Category::create([
            'name' => $this->categoryForm['name'],
            'slug' => $this->categoryForm['slug'],
        ]);
        $this->categories = Category::get();
        $this->resetCategoryForm();
        $this->dispatch('saved');
    }

    /**
     * Delete a category.
     */
    public function deleteCategory($categoryId) {
        $this->post['categories'] = array_diff($this->post['categories'], [$categoryId]);
        $category = Category::findOrFail($categoryId);
        $category->posts()->detach();
        $category->delete();
        $this->categories = Category::get();
        $this->resetCategoryForm();
    }

    /**
     * Update an existing category.
     */
    public function updateCategory($categoryId) {
        $this->categoryForm['id'] = $categoryId;
        $this->validate($this->categoryRules(), $this->categoryMessages());
        // Check for duplicate slug (excluding current category)
        if (Category::where('slug', $this->categoryForm['slug'])->where('id', '!=', $categoryId)->exists()) {
            $this->addError('categoryForm.slug', 'Category slug must be unique');
            return;
        }
        $category = Category::findOrFail($categoryId);
        $category->update([
            'name' => $this->categoryForm['name'],
            'slug' => $this->categoryForm['slug'],
        ]);
        $this->categories = Category::get();
        $this->resetCategoryForm();
    }

    /**
     * Edit a category (populate the form with category data).
     */
    public function editCategory($categoryId) {
        $category = Category::findOrFail($categoryId);
        $this->categoryForm = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ];
        $this->showCategoryModal = true;
    }

    public function openCategoryModal() {
        $this->showCategoryModal = true;
        $this->categoryForm = [
            'id' => null,
            'name' => '',
            'slug' => '',
        ];
    }

    /**
     * Reset the category form.
     */
    public function resetCategoryForm() {
        $this->categoryForm = [
            'id' => null,
            'name' => '',
            'slug' => '',
        ];
        $this->showCategoryModal = false;
    }

    public function clearFilters() {
        $this->filters = [
            'search' => '',
            'category' => null,
            'status' => null,
        ];
        $this->dispatch('component-updated');
    }

    /**
     * Render the component view.
     */
    public function render() {
        if ($this->filters['status'] == '') {
            $this->filters['status'] = null; // Ensure status is null if empty
        }
        $posts = Post::query()
            ->when($this->filters['search'], function ($query) {
                $query->where('title', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('slug', 'like', '%' . $this->filters['search'] . '%');
            })
            ->when($this->filters['category'], function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('id', $this->filters['category']);
                });
            })
            ->when($this->filters['status'] !== null, function ($query) {
                $query->where('is_published', $this->filters['status']);
            })
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('backend.blog.manage', [
            'posts' => $posts,
        ]);
    }
}
