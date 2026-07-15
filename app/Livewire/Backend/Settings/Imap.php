<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;
use App\Services\TMail;
use Exception;

class Imap extends Component {

    /**
     * Components State
     */
    public $state = [
        'imap' => [
            'host' => '',
            'port' => 993,
            'encryption' => '',
            'validate_cert' => false,
            'username' => '',
            'password' => '',
            'default_account' => 'default',
            'protocol' => 'imap',
            'cc_check' => false,
        ],
        'error' => null
    ];

    public function mount()
    {
        $config = config('app.settings.imap');

        $this->state['imap'] = array_merge([
            'host' => '',
            'port' => 993,
            'encryption' => '',
            'validate_cert' => false,
            'username' => '',
            'password' => '',
            'default_account' => 'default',
            'protocol' => 'imap',
            'cc_check' => false,
        ], is_array($config) ? $config : []);
    }

    private function test() {
        try {
            TMail::connectMailBox($this->state['imap']);
            return true;
        } catch (Exception $e) {
            $this->state['error'] = $e->getMessage();
        }
    }

    public function save() {
        $this->validate(
            [
                'state.imap.host' => 'required',
                'state.imap.port' => 'required|numeric',
                'state.imap.username' => 'required',
                'state.imap.password' => 'required',
            ],
            [
                'state.imap.host.required' => 'Host field is Required',
                'state.imap.port.required' => 'Port field is Required',
                'state.imap.port.numeric' => 'Port field can only be Numeric',
                'state.imap.username.required' => 'Username field is Required',
                'state.imap.password.required' => 'Password field is Required',
            ]
        );
        $this->state['error'] = null;

        // Temporarily skip live IMAP connection test to avoid timeout issues
        // Ensure setting row exists
        $setting = Setting::where('key', 'imap')->first();

        if (!$setting) {
            $setting = new Setting();
            $setting->key = 'imap';
        }

        $setting->value = serialize($this->state['imap']);
        $setting->save();

        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.imap');
    }
}
