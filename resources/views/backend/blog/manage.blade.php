<div>
    <form wire:submit.prevent="savePost" x-data="{ syncSlug: true, justUpdatedSlug: false }" class="flex flex-col {{ $addPost || $updatePost ? "" : "hidden" }}">
        <div class="flex justify-between items-center sticky top-0 bg-gray-100 dark:bg-gray-900 py-4 z-10">
            <x-secondary-button-icon type="button" position="left" icon="hgi-arrow-left-02" wire:click="clearAddUpdate">{{ __("Back") }}</x-secondary-button-icon>
            <div class="flex items-center gap-2">
                <x-action-message class="mr-3" on="saved">
                    {{ __("Saved.") }}
                </x-action-message>
                <x-button>{{ __("Save") }}</x-button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 flex flex-col gap-6">
                <x-card class="flex flex-col gap-4 p-6">
                    <div>
                        <x-label for="title" value="{{ __('Name') }}" />
                        <x-input id="title" type="text" class="mt-1 block w-full" placeholder="eg. Hello World" x-on:input="if (syncSlug) { $refs.slug.value = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); justUpdatedSlug = true; $refs.slug.dispatchEvent(new Event('input', { bubbles: true })); }" wire:model="post.title" />
                        <x-input-error for="post.title" class="mt-2" />
                    </div>
                    @if (! isset($post["lang"]))
                        <div>
                            <x-label for="slug" value="{{ __('Slug') }}" />
                            <x-input-prefix prefix="{{ config('app.url') }}/blog/" type="text" placeholder="hello-world" x-ref="slug" x-on:input="if (justUpdatedSlug) { justUpdatedSlug = false } else { syncSlug = false }" wire:model="post.slug" />
                            <x-input-error for="post.slug" class="mt-2" />
                        </div>
                    @endif

                    <div x-data="{ show: false }">
                        <x-label for="content" value="{{ __('Content') }}" />
                        <textarea class="hidden" id="post-content" wire:model="post.content"></textarea>
                        <div
                            class="mt-1"
                            wire:ignore
                            x-data
                            x-init="
                                tinymce.init({
                                    skin: localStorage.getItem('darkmode') == 'enabled' ? 'oxide-dark' : 'oxide',
                                    content_css: localStorage.getItem('darkmode') == 'enabled' ? 'dark' : 'default',
                                    selector: '#tinymce-editor',
                                    license_key: 'gpl',
                                    plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap nonbreaking anchor insertdatetime advlist lists wordcount help',
                                    toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | link image media | table | code fullscreen preview',
                                    menubar: 'file edit view insert format tools table help',
                                    height: 300,
                                    setup(editor) {
                                        editor.on('Change KeyUp', () => {
                                            $wire.set('post.content', editor.getContent())
                                        })
                                    },
                                    autosave_ask_before_unload: false,
                                    file_picker_types: 'image',
                                    images_file_types: 'jpeg,png,jpg,gif,webp',
                                    relative_urls: false,
                                    remove_script_host: false,
                                    images_upload_handler: async (blobInfo, success, failure) => {
                                        try {
                                            const formData = new FormData();
                                            formData.append('file', blobInfo.blob(), blobInfo.filename());
        
                                            const response = await fetch('/admin/upload/tinymce/image', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                                },
                                                body: formData
                                            });
                                            const json = await response.json();
                                            if (json.location) {
                                                return json.location;
                                            } else if (json.error) {
                                                throw new Error(json.error);
                                            } else {
                                                throw new Error('Upload failed: Invalid server response');
                                            }
                                        } catch (error) {
                                            throw new Error(error.message);
                                        }
                                    }
                                })
                            "
                        >
                            <textarea id="tinymce-editor">{{ $post["content"] }}</textarea>
                        </div>
                        <x-input-error for="post.content" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="excerpt" value="{{ __('Excerpt') }} " />
                        <x-textarea maxlength="255" id="excerpt" class="mt-1 block w-full resize-y" placeholder="eg. This is a short description of the post." wire:model="post.excerpt"></x-textarea>
                        <x-input-error for="post.excerpt" class="mt-2" />
                    </div>
                </x-card>
                <x-card class="flex flex-col gap-4 p-6">
                    <div>
                        <x-label value="{{ __('Meta Tags') }} " />
                        @foreach ($post["meta"] as $key => $meta)
                            <div class="flex mt-1">
                                <div>
                                    <x-label class="font-medium text-xs">{{ __("Name") }}</x-label>
                                    <div class="relative">
                                        <x-select class="mt-1 block w-full" wire:model="post.meta.{{ $key }}.name">
                                            <option value="" disabled selected>Select</option>
                                            <option value="description">{{ __("description") }}</option>
                                            <option value="robots">{{ __("robots") }}</option>
                                            <option value="canonical">{{ __("canonical") }}</option>
                                            <option value="og:type">{{ __("og:type") }}</option>
                                            <option value="og:title">{{ __("og:title") }}</option>
                                            <option value="og:description">{{ __("og:description") }}</option>
                                            <option value="og:image">{{ __("og:image") }}</option>
                                            <option value="og:url">{{ __("og:url") }}</option>
                                            <option value="og:site_name">{{ __("og:site_name") }}</option>
                                            <option value="twitter:title">{{ __("twitter:title") }}</option>
                                            <option value="twitter:description">{{ __("twitter:description") }}</option>
                                            <option value="twitter:image">{{ __("twitter:image") }}</option>
                                            <option value="twitter:site">{{ __("twitter:site") }}</option>
                                            <option value="twitter:creator">{{ __("twitter:creator") }}</option>
                                        </x-select>
                                    </div>
                                    <x-input-error for="post.meta.{{ $key }}.name" class="mt-2" />
                                </div>
                                <div class="flex-1 ml-3">
                                    <x-label class="font-medium text-xs">{{ __("Content") }}</x-label>
                                    <div class="flex gap-3">
                                        <x-input type="text" class="mt-1 block w-full" wire:model="post.meta.{{ $key }}.content" />
                                        <x-button type="button" wire:click="deleteMeta({{ $key }})" style="error" class="mt-1"><i class="hgi hgi-stroke hgi-delete-02"></i></x-button>
                                    </div>
                                    <x-input-error for="post.meta.{{ $key }}.content" class="mt-2" />
                                </div>
                            </div>
                        @endforeach

                        <x-button type="button" wire:click="addMeta" style="success" class="mt-3">{{ __("Add") }}</x-button>
                    </div>
                    <div>
                        <x-label for="header" value="{{ __('Custom Header') }} " />
                        <x-textarea id="header" class="mt-1 block w-full resize-y" placeholder="eg. <meta name='author' content='John Doe'>" wire:model="post.header"></x-textarea>
                        <x-input-error for="post.header" class="mt-2" />
                    </div>
                </x-card>
                @if (isset($post["id"]) && ! isset($post["lang"]))
                    <x-card class="p-6">
                        <div>
                            <x-label for="lang" value="{{ __('Add Translations') }} " />
                            <div class="grid gap-1 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 mt-2">
                                @foreach (config("app.settings.languages") as $language => $details)
                                    @if ($language != config("app.settings.language"))
                                        <button wire:click="translate('{{ $language }}')" type="button" class="{{ $this->isTranslated($language) ? "bg-green-500" : "bg-gray-900" }} text-white text-sm px-3 py-2 rounded-md flex items-center space-x-2">
                                            @if ($this->isTranslated($language))
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            @endif
                                            <span>{{ $details["label"] }}</span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </x-card>
                @endif
            </div>
            <div class="flex flex-col gap-6">
                @if (isset($post["lang"]))
                    <x-card class="p-6">
                        <div class="py-1 flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                            </svg>
                            <span>{{ __("Add translations for") }} {{ $post["lang_text"] }} ({{ $post["lang"] }})</span>
                        </div>
                    </x-card>
                @endif

                @if (! isset($post["lang"]))
                    <x-card class="p-6">
                        <div>
                            <x-label for="status">{{ __("Status") }}</x-label>
                            <x-select id="status" class="mt-1 block w-full" wire:model="post.is_published">
                                <option value="0">{{ __("Draft") }}</option>
                                <option value="1">{{ __("Published") }}</option>
                            </x-select>
                        </div>
                    </x-card>

                    <x-card class="p-6">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <x-label value="{{ __('Categories') }}" class="mr-2" />
                                <button type="button" class="ml-2 mt-1" wire:click="openCategoryModal">
                                    <i class="hgi hgi-stroke hgi-plus-sign-square"></i>
                                </button>
                            </div>
                            <div class="flex flex-col gap-2 mb-2">
                                @foreach ($categories as $cat)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <div class="flex items-center">
                                            <x-checkbox class="mr-1" value="{{ $cat->id }}" wire:model="post.categories" />
                                            <div class="text-sm">{{ $cat->name }}</div>
                                        </div>
                                        <button type="button" wire:click="editCategory({{ $cat->id }})" class="text-xs" title="Edit">
                                            <i class="hgi hgi-stroke hgi-edit-03 mt-1"></i>
                                        </button>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error for="post.categories" class="mt-2" />
                        </div>
                    </x-card>

                    <x-card class="p-6">
                        <div>
                            <x-label for="logo" value="{{ __('Feature Image') }}" />

                            @if ($image || $post["image"])
                                <div class="relative">
                                    <x-button-icon class="absolute top-2 right-2" icon="hgi-delete-02" position="left" type="button" wire:click="clearImage" style="error"></x-button-icon>
                                    <img class="max-w-full rounded mt-2" src="{{ $image ? $image->temporaryUrl() : $post["image"] }}" />
                                </div>
                            @else
                                <div class="flex items-center justify-center w-full mt-2">
                                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <div class="text-3xl mb-4 text-gray-500 dark:text-gray-400">
                                                <i class="hgi hgi-stroke hgi-image-upload"></i>
                                            </div>
                                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">{{ __("Click to Upload") }}</span>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __("WEBP, PNG, JPG or GIF upto 2MB") }}</p>
                                        </div>
                                        <input id="dropzone-file" type="file" class="hidden" wire:model="image" accept=".png, .jpg, .jpeg, .gif, .webp" />
                                    </label>
                                </div>
                                <x-input-error for="logo" class="mt-2" />
                            @endif
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </form>

    <div class="{{ $addPost || $updatePost ? "hidden" : "" }}">
        @if (count($posts) > 0 || $filters != ["search" => "", "category" => null, "status" => null])
            <div class="flex justify-end -mt-24 mb-16 pr-5 md:pr-0">
                <x-button type="button" wire:click="showPostForm">{{ __("Add Post") }}</x-button>
            </div>
        @endif

        <div class="flex flex-col gap-6">
            @if (count($posts) == 0 && $filters == ["search" => "", "category" => null, "status" => null])
                <div class="flex flex-col items-center gap-16 py-10">
                    <img class="max-w-sm" src="{{ asset("images/illustrations/undraw_typewriter_d4km.svg") }}" alt="add_blog" />
                    <div class="text-center">
                        <div class="text-gray-800 dark:text-gray-200 mb-4">{{ __("Hmm.. We can't find any blog posts. Let's get started with your first one!") }}</div>
                        <x-button type="button" wire:click="showPostForm" class="mt-4">{{ __("Add Post") }}</x-button>
                    </div>
                </div>
            @else
                <div class="flex flex-wrap gap-4">
                    <!-- Search Input: Full width on small screens -->
                    <div class="w-full md:w-auto flex-1">
                        <x-input size="sm" type="text" placeholder="{{ __('Search by Post Title or Slug') }}" wire:model.debounce.300ms="filters.search" class="w-full" />
                    </div>
                    <!-- Other controls: Stack below on mobile, inline on md+ -->
                    <div class="flex flex-wrap gap-4 w-full md:w-auto">
                        <x-select size="sm" wire:model="filters.category" class="grow md:grow-0">
                            <option value="">{{ __("All Categories") }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-select>
                        <x-select size="sm" wire:model="filters.status" class="grow md:grow-0">
                            <option value="">{{ __("All Statuses") }}</option>
                            <option value="1">{{ __("Published") }}</option>
                            <option value="0">{{ __("Draft") }}</option>
                        </x-select>
                        <x-button type="button" wire:click="clearPostObject">
                            <i class="hgi hgi-stroke hgi-search-01 py-1"></i>
                        </x-button>
                        @if ($filters != ["search" => "", "category" => null, "status" => null])
                            <x-button style="error" type="button" wire:click="clearFilters">
                                <i class="hgi hgi-stroke hgi-cancel-01 py-1"></i>
                            </x-button>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($posts as $post)
                        <x-card class="overflow-hidden flex flex-col h-full">
                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex items-center justify-between text-xs mb-4">
                                    <div class="flex items-center gap-1">
                                        <i class="hgi hgi-stroke hgi-calendar-03"></i>
                                        {{ $post->created_at->format("M d, Y") }}
                                    </div>

                                    @if ($post->is_published)
                                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-xs font-medium rounded-full">{{ __("Published") }}</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-900 text-xs font-medium rounded-full">{{ __("Draft") }}</span>
                                    @endif
                                </div>

                                <h3 class="text-xl font-semibold mb-2 line-clamp-2">{{ $post->title }}</h3>
                                <p class="text-sm mb-4 line-clamp-3">{{ $post->excerpt }}</p>
                                <a href="{{ config("app.url") }}/blog/{{ $post->slug }}" target="_blank" class="block text-gray-500 font-medium text-xs mb-4">/blog/{{ $post->slug }}</a>

                                <!-- Button section stuck at bottom -->
                                <div class="flex gap-2 mt-auto">
                                    <x-button-icon class="flex-1 justify-center" icon="hgi-edit-03" position="left" type="button" wire:click="showUpdate({{ $post->id }})">{{ __("Edit") }}</x-button-icon>
                                    <x-button-icon class="justify-center" style="error" icon="hgi-delete-01" position="left" type="button" wire:click="$dispatch('confirm-delete', '{{ $post->id }}')"></x-button-icon>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
                {{ $posts->links() }}
            @endif
        </div>
    </div>

    <!-- Category Modal -->
    <x-dialog-modal wire:model="showCategoryModal">
        <x-slot name="title">
            {{ $categoryForm["id"] ? __("Update Category") : __("Add Category") }}
        </x-slot>
        <x-slot name="content">
            <div x-data="{ syncCatSlug: true, justUpdatedCatSlug: false }">
                <div class="mb-4">
                    <x-label for="category-name" value="{{ __('Name') }}" />
                    <x-input id="category-name" type="text" class="mt-1 block w-full" placeholder="eg. News" x-on:input="if (syncCatSlug) { $refs.catSlug.value = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); justUpdatedCatSlug = true; $refs.catSlug.dispatchEvent(new Event('input', { bubbles: true })); }" wire:model="categoryForm.name" />
                    <x-input-error for="categoryForm.name" class="mt-2" />
                    <small class="flex items-center gap-1">
                        {{ __("Manage translation for Category Name in Settings -> Languages -> Specific Language ->") }}
                        <i class="hgi hgi-stroke hgi-settings-02"></i>
                    </small>
                </div>
                <div>
                    <x-label for="category-slug" value="{{ __('Slug') }}" />
                    <x-input-prefix prefix="{{ config('app.url') }}/category/" type="text" id="category-slug" placeholder="news" x-ref="catSlug" x-on:input="if (justUpdatedCatSlug) { justUpdatedCatSlug = false } else { syncCatSlug = false }" wire:model="categoryForm.slug" />
                    <x-input-error for="categoryForm.slug" class="mt-2" />
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button type="button" wire:click="$set('showCategoryModal', false)">
                {{ __("Cancel") }}
            </x-secondary-button>
            @if ($categoryForm["id"])
                <x-button type="button" style="error" class="ml-2" wire:click="deleteCategory({{ $categoryForm['id'] }})">
                    {{ __("Delete") }}
                </x-button>
                <x-button type="button" class="ml-2" wire:click="updateCategory({{ $categoryForm['id'] }})">
                    {{ __("Update") }}
                </x-button>
            @else
                <x-button type="button" class="ml-2" wire:click="addCategory">
                    {{ __("Add") }}
                </x-button>
            @endif
        </x-slot>
    </x-dialog-modal>

    @script
        <script>
            $wire.on('confirm-delete', (postId) => {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("You will not be able to recover this post!") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, delete it!") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.call('delete', postId);
                    }
                });
            });
            $wire.on('component-updated', () => {
                setTimeout(() => {
                    let content = document.querySelector('textarea#post-content').value;
                    tinymce.get('tinymce-editor').setContent(content);
                }, 100);
            });
        </script>
    @endscript
</div>
