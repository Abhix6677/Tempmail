<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;

class Socials extends Component {

    /**
     * Components State
     */
    public $state = [
        'socials' => []
    ];

    public function mount() {
        $this->state['socials'] = config('app.settings.socials');
    }

    public function add() {
        array_push($this->state['socials'], [
            'icon' => '',
            'link' => ''
        ]);
    }

    public function remove($key) {
        unset($this->state['socials'][$key]);
    }

    public function save() {
        $this->validate(
            [
                'state.socials.*.icon' => 'required',
                'state.socials.*.link' => 'required',
            ],
            [
                'state.socials.*.icon.required' => 'Icon field is Required',
                'state.socials.*.link.required' => 'Link field is Required',
            ]
        );
        $setting = Setting::where('key', 'socials')->first();
        $setting->value = serialize($this->state['socials']);
        $setting->save();
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.socials');
    }
}
