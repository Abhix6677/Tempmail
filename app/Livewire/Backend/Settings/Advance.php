<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Advance extends Component {
    /**
     * Components State
     */
    public $state = [
        'lock' => [
            'enable' => false,
            'text' => '',
            'password' => ''
        ],
        'global' => [
            'css' => '',
            'js' => '',
            'header' => '',
            'footer' => ''
        ],
        'api_keys' => [],
    ];

    public function mount() {
        $this->state['lock'] = config('app.settings.lock') ?? [];
        $this->state['global'] = config('app.settings.global') ?? [];
        $this->state['api_keys'] = config('app.settings.api_keys') ?? [];

        // Normalize lock structure
        $this->state['lock'] = array_merge([
            'enable' => false,
            'text' => '',
            'password' => ''
        ], $this->state['lock'] ?? []);

        // Normalize global structure
        $this->state['global'] = array_merge([
            'css' => '',
            'js' => '',
            'header' => '',
            'footer' => ''
        ], $this->state['global'] ?? []);

        if (!is_array($this->state['api_keys'])) {
            $this->state['api_keys'] = [];
        }
    }

    public function add() {
        array_push($this->state['api_keys'], $this->random());
    }

    public function remove($key) {
        unset($this->state['api_keys'][$key]);
    }

    private function random($length = 20) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }

    public function save() {
        $this->validate(
            [
                'state.api_keys.*' => 'required'
            ],
            [
                'state.api_keys.*.required' => 'API Key field is Required'
            ]
        );
        $settings = Setting::whereIn('key', ['lock', 'global', 'api_keys'])->get();
        foreach ($settings as $setting) {
            $setting->value = serialize($this->state[$setting->key]);
            $setting->save();
        }
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.advance');
    }
}
