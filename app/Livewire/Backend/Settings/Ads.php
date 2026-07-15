<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;

class Ads extends Component {

    /**
     * Components State
     */
    public $state = [
        'ads' => [
            'one' => '',
            'two' => '',
            'three' => '',
            'four' => '',
            'five' => ''
        ]
    ];

    public function mount() {
        $this->state['ads'] = config('app.settings.ads');
    }

    public function save() {
        $setting = Setting::where('key', 'ads')->first();
        $setting->value = serialize($this->state['ads']);
        $setting->save();
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.ads');
    }
}
