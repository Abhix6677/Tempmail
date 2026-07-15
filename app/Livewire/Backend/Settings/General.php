<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

class General extends Component {

    use WithFileUploads;

    /**
     * Components State
     */
    public $state = [
        'name' => '',
        'license_key' => '',
        'pages' => [],
        'homepage' => 0,
        'app_header' => '',
        'colors' => [
            'primary' => '#000000',
            'secondary' => '#000000',
            'tertiary' => '#000000'
        ],
        'enable_dark_mode' => false,
        'user_registration' => [
            'enable' => false,
            'enabled' => false,
            'require_email_verification' => false,
        ],
        'cookie' => [
            'enable' => false,
        ],
        'custom_logo' => '',
        'custom_favicon' => '',
        'enable_create_from_url' => false,
        'disable_mailbox_slug' => false,
        'external_link_masker' => '',
        'custom_external_link_masker' => '',
        'enable_ad_block_detector' => false,
        'font_family' => [
            'head' => 'Kadwa',
            'body' => 'Poppins',
        ],
        "disqus" => [
            "enable" => false,
            "shortname" => "",
        ],
    ];

    public $logo, $favicon;

    public function mount() {
        $pages = Page::all();
        foreach ($pages as $page) {
            $this->state['pages'][$page->id] = $page->title;
        }
        foreach ($this->state as $key => $value) {
            if (!in_array($key, ['pages'])) {
                $configValue = config('app.settings.' . $key);
                if ($configValue !== null) {
                    $this->state[$key] = $configValue;
                }
            }
        }

        // Ensure colors structure always exists with defaults
        $this->state['colors'] = array_merge([
            'primary' => '#3b82f6',
            'secondary' => '#64748b',
            'tertiary' => '#000000',
        ], $this->state['colors'] ?? []);

        // Normalize cookie structure to prevent undefined key errors
        $this->state['cookie'] = array_merge([
            'enable' => false,
        ], $this->state['cookie'] ?? []);

        // Normalize user_registration structure to prevent undefined key errors
        $this->state['user_registration'] = array_merge([
            'enable' => false,
            'enabled' => false,
            'require_email_verification' => false,
        ], $this->state['user_registration'] ?? []);

        // Keep backward compatibility between "enable" and "enabled"
        if (isset($this->state['user_registration']['enable']) && !isset($this->state['user_registration']['enabled'])) {
            $this->state['user_registration']['enabled'] = $this->state['user_registration']['enable'];
        }

        if (isset($this->state['user_registration']['enabled']) && !isset($this->state['user_registration']['enable'])) {
            $this->state['user_registration']['enable'] = $this->state['user_registration']['enabled'];
        }
        $logo = Setting::pick('logo');
        if ($logo) {
            $this->state['custom_logo'] = Storage::url($logo);
        } else if (Storage::exists('public/images/custom-logo.png')) {
            $this->state['custom_logo'] = Storage::url('public/images/custom-logo.png');
        }
        $favicon = Setting::pick('favicon');
        if ($favicon) {
            $this->state['custom_favicon'] = Storage::url($favicon);
        } else if (Storage::exists('public/images/custom-favicon.png')) {
            $this->state['custom_favicon'] = Storage::url('public/images/custom-favicon.png');
        }
    }

    public function save() {
        $this->validate(
            [
                'state.name' => 'required',
                'state.license_key' => 'required',
                'state.logo' => 'image|max:1024',
                'state.favicon' => 'image|max:1024',
                'state.font_family.head' => 'required',
                'state.font_family.body' => 'required',
            ],
            [
                'state.name.required' => 'App Name is Required',
                'state.license_key.required' => 'License Key is Required',
                'state.logo.image' => 'Invalid Logo file',
                'state.logo.max' => 'Max Size is 1MB',
                'state.favicon.image' => 'Invalid Logo file',
                'state.favicon.max' => 'Max Size is 1MB',
                'state.font_family.head' => 'Heading Font Family is Required',
                'state.font_family.body' => 'Body Font Family is Required',
            ]
        );
        if ($this->logo) {
            $logo = $this->logo->storeAs('images', $this->logo->getClientOriginalName(), 'public');
            Setting::put('logo', $logo);
        }
        if ($this->favicon) {
            $favicon = $this->favicon->storeAs('images', $this->favicon->getClientOriginalName(), 'public');
            Setting::put('favicon', $favicon);
        }
        if ($this->state['homepage'] != 0) {
            $this->state['disable_mailbox_slug'] = false;
        }
        if ($this->state['external_link_masker'] == 'custom') {
            $this->state['external_link_masker'] = $this->state['custom_external_link_masker'];
        }
        $settings = Setting::whereIn('key', ['name', 'license_key', 'user_registration', 'homepage', 'app_header', 'colors', 'enable_dark_mode', 'cookie', 'enable_create_from_url', 'disable_mailbox_slug', 'external_link_masker', 'enable_ad_block_detector', 'font_family', 'disqus'])->get();
        foreach ($settings as $setting) {
            $setting->value = serialize($this->state[$setting->key]);
            $setting->save();
        }
        $this->dispatch('saved');
    }

    public function render() {
        if (!in_array($this->state['external_link_masker'], ['', 'https://relink.cc', 'custom'])) {
            $this->state['custom_external_link_masker'] = $this->state['external_link_masker'];
            $this->state['external_link_masker'] = 'custom';
        }
        return view('backend.settings.general');
    }
}
