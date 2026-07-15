<div>
    @if (count($pages) > 0 && ! ($addPage || $updatePage))
        <div class="flex justify-end -mt-24 mb-16 pr-5 md:pr-0">
            <x-button type="button" wire:click="showPageForm">{{ __("Add Page") }}</x-button>
        </div>
    @endif

    <div class="{{ $addPage || $updatePage ? "" : "hidden" }}">
        <form wire:submit.prevent="savePage" x-data="{ syncSlug: true, justUpdatedSlug: false }">
            <div class="flex justify-between items-center sticky top-0 bg-gray-100 dark:bg-gray-900 py-4 z-10">
                <x-secondary-button-icon type="button" position="left" icon="hgi-arrow-left-02" wire:click="clearAddUpdate">{{ __("Back") }}</x-secondary-button-icon>
                <div class="flex items-center gap-2">
                    <x-action-message class="mr-3" on="saved">
                        {{ __("Saved.") }}
                    </x-action-message>
                    <x-button>{{ __("Save") }}</x-button>
                </div>
            </div>
            <x-card class="p-6 grid grid-cols-6 gap-6">
                @if (isset($page["lang"]))
                    <div class="col-span-6 flex">
                        <div class="border-b border-dashed border-gray-500 py-1 flex items-center gap-3 text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                            </svg>
                            <span>{{ __("Add translations for") }} {{ $page["lang_text"] }} ({{ $page["lang"] }})</span>
                        </div>
                    </div>
                @endif

                <div class="col-span-6">
                    <x-label for="title" value="{{ __('Name') }}" />
                    @if (isset($page["id"]))
                        <x-input id="title" type="text" class="mt-1 block w-full" placeholder="eg. About Us" wire:model="page.title" />
                    @else
                        <x-input id="title" type="text" class="mt-1 block w-full" placeholder="eg. About Us" x-on:input="if (syncSlug) { $refs.slug.value = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); justUpdatedSlug = true; $refs.slug.dispatchEvent(new Event('input', { bubbles: true })); }" wire:model="page.title" />
                    @endif
                    <x-input-error for="page.title" class="mt-2" />
                </div>
                @if (! isset($page["lang"]))
                    <div class="col-span-6">
                        <x-label for="slug" value="{{ __('Slug') }}" />
                        <x-input-prefix prefix="{{ config('app.url') }}/" type="text" placeholder="about-us" x-ref="slug" x-on:input="if (justUpdatedSlug) { justUpdatedSlug = false } else { syncSlug = false }" wire:model="page.slug" />
                        <x-input-error for="page.slug" class="mt-2" />
                    </div>
                    <div class="col-span-6">
                        <x-label class="font-medium text-xs">{{ __("Status") }}</x-label>
                        <x-select class="mt-1 block w-full" wire:model="page.is_published">
                            <option value="0">{{ __("Draft") }}</option>
                            <option value="1">{{ __("Published") }}</option>
                        </x-select>
                    </div>
                @endif

                <div class="col-span-6" x-data="{ show: false }">
                    <x-label for="content" value="{{ __('Content') }}" />
                    <textarea class="hidden" id="page-content" wire:model="page.content"></textarea>
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
                            plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help',
                            toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | link image media | table | code fullscreen preview',
                            menubar: 'file edit view insert format tools table help',
                            height: 300,
                            setup(editor) {
                                editor.on('Change KeyUp', () => {
                                    $wire.set('page.content', editor.getContent())
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
                        <textarea id="tinymce-editor">{{ $page["content"] }}</textarea>
                    </div>
                    <x-input-error for="page.content" class="mt-2" />
                    <small class="block mt-2">
                        {{ __("Available Shortcodes:") }}
                        <span class="cursor-pointer" title="Click to Copy" @click="navigator.clipboard.writeText('[contact_form]')">{{ __("[contact_form]") }}</span>
                        <span class="cursor-pointer" title="Click to Copy" @click="navigator.clipboard.writeText('[blogs]')">{{ __("[blogs]") }}</span>
                        -
                        <a href="https://helpdesk.thehp.in/hc/articles/9/18/6/embed-wordpress-blogs" target="_blank">{{ __("More Info") }}</a>
                    </small>
                </div>
                <div class="col-span-6">
                    <x-label value="{{ __('Meta Tags') }} " />
                    @foreach ($page["meta"] as $key => $meta)
                        <div class="flex mt-2">
                            <div>
                                <x-label class="font-medium text-xs">{{ __("Name") }}</x-label>
                                <x-select class="mt-1 block w-full" wire:model="page.meta.{{ $key }}.name">
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
                                <x-input-error for="page.meta.{{ $key }}.name" class="mt-2" />
                            </div>
                            <div class="flex-1 ml-3">
                                <x-label class="font-medium text-xs">{{ __("Content") }}</x-label>
                                <div class="flex gap-3">
                                    <x-input type="text" class="mt-1 block w-full" wire:model="page.meta.{{ $key }}.content" />
                                    <x-button type="button" wire:click="deleteMeta({{ $key }})" style="error" class="mt-1"><i class="hgi hgi-stroke hgi-delete-02"></i></x-button>
                                </div>
                                <x-input-error for="page.meta.{{ $key }}.content" class="mt-2" />
                            </div>
                        </div>
                    @endforeach

                    <x-button type="button" wire:click="addMeta" style="success" class="mt-3">{{ __("Add") }}</x-button>
                </div>
                <div class="col-span-6">
                    <x-label for="header" value="{{ __('Custom Header') }} " />
                    <x-textarea id="header" class="mt-1 block w-full resize-y" placeholder="eg. <meta name='author' content='John Doe'>" wire:model="page.header"></x-textarea>
                    <x-input-error for="page.header" class="mt-2" />
                </div>
                @if (isset($page["id"]) && ! isset($page["lang"]))
                    <div class="col-span-6">
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
                @endif
            </x-card>
        </form>
    </div>

    <div class="flex flex-col gap-6 {{ $addPage || $updatePage ? "hidden" : "" }}">
        @if (count($pages) == 0 && $filters == ["search" => "", "status" => null])
            <div class="flex flex-col items-center gap-16 py-10">
                <img class="max-w-sm" src="{{ asset("images/illustrations/undraw_content-creator_vuqg.svg") }}" alt="add_blog" />
                <div class="text-center">
                    <div class="text-gray-800 dark:text-gray-200 mb-4">{{ __("Oh! There are no pages here. Let's create one!") }}</div>
                    <x-button type="button" wire:click="showPageForm" class="mt-4">{{ __("Add Page") }}</x-button>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between gap-4">
                <x-input size="sm" type="text" placeholder="{{ __('Search by Page Title or Slug') }}" wire:model.debounce.300ms="filters.search" class="flex-1 w-64"></x-input>
                <x-select size="sm" wire:model="filters.status">
                    <option value="">{{ __("All Statuses") }}</option>
                    <option value="1">{{ __("Published") }}</option>
                    <option value="0">{{ __("Draft") }}</option>
                </x-select>
                <x-button type="button" wire:click="clearPageObject"><i class="hgi hgi-stroke hgi-search-01 py-1"></i></x-button>
                @if ($filters != ["search" => "", "status" => null])
                    <x-button style="error" type="button" wire:click="clearFilters"><i class="hgi hgi-stroke hgi-cancel-01 py-1"></i></x-button>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($pages as $page)
                    <x-card class="overflow-hidden p-6">
                        <div class="flex items-center justify-between text-xs mb-4">
                            <div class="flex items-center gap-1">
                                <i class="hgi hgi-stroke hgi-calendar-03"></i>
                                {{ $page->created_at->format("M d, Y") }}
                            </div>

                            @if ($page->is_published)
                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-xs font-medium rounded-full">{{ __("Published") }}</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-900 text-xs font-medium rounded-full">{{ __("Draft") }}</span>
                            @endif
                        </div>

                        <h3 class="text-xl font-semibold mb-2 line-clamp-2">{{ $page->title }}</h3>
                        <a href="{{ config("app.url") }}/{{ $page->slug }}" target="_blank" class="block text-gray-500 font-medium text-xs mb-4">/{{ $page->slug }}</a>
                        <div class="flex gap-2">
                            <x-button-icon class="flex-1 justify-center" icon="hgi-edit-03" position="left" type="button" wire:click="showUpdate({{ $page->id }})">{{ __("Edit") }}</x-button-icon>
                            <x-button-icon class="justify-center" style="error" icon="hgi-delete-01" position="left" type="button" wire:click="$dispatch('confirm-delete', '{{ $page->id }}')"></x-button-icon>
                        </div>
                    </x-card>
                @endforeach
            </div>
            {{ $pages->links() }}
        @endif
    </div>

    @script
        <script>
            $wire.on('confirm-delete', (pageId) => {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("You will not be able to recover this page!") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, delete it!") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.call('delete', pageId);
                    }
                });
            });
            $wire.on('component-updated', () => {
                setTimeout(() => {
                    let content = document.querySelector('textarea#page-content').value;
                    tinymce.get('tinymce-editor').setContent(content);
                }, 100);
            });
        </script>
    @endscript
</div>
