<?php

namespace App\Livewire\Backend\Settings;

use App\Models\Page;
use Livewire\Component;
use App\Models\Setting;

class Theme extends Component {

    /**
     * Components State
     */
    public $state = [
        'theme_options' => [
            'mailbox_page' => '',
            'button' => [
                'text' => '',
                'link' => '',
            ]
        ],
        'pages' => []
    ];

    public function mount() {
        if (config('app.settings.theme') == 'groot' || config('app.settings.theme') == 'drax') {
            $pages = Page::all();
            foreach ($pages as $page) {
                $this->state['pages'][$page->id] = $page->title;
            }
        }
        $this->state['theme_options'] = config('app.settings.theme_options');
    }

    public function save() {
        $setting = Setting::where('key', 'theme_options')->first();
        $setting->value = serialize($this->state['theme_options']);
        $setting->save();
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.theme');
    }
}
