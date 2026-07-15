<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;

class Mail extends Component {

    /**
     * Components State
     */
    public $state = [
        'mail' => [
            'type' => '',
            'smtp' => [
                'host' => '',
                'port' => '',
                'username' => '',
                'password' => '',
                'encryption' => '',
            ],
            'from' => [
                'address' => '',
                'name' => ''
            ]
        ],
    ];

    public function mount() {
        \Log::info('Mail component mount start');

        $this->state['mail']['type'] = config('mail.default') ?? 'log';

        $smtp = config('mail.mailers.smtp') ?? [];
        $this->state['mail']['smtp'] = [
            'host' => $smtp['host'] ?? '',
            'port' => $smtp['port'] ?? '',
            'username' => $smtp['username'] ?? '',
            'password' => '',
            'encryption' => $smtp['encryption'] ?? '',
        ];

        $from = config('mail.from') ?? [];
        $this->state['mail']['from'] = [
            'address' => $from['address'] ?? '',
            'name' => $from['name'] ?? '',
        ];

        \Log::info('Mail component mount end');
    }

    public function save() {
        $this->validate(
            [
                'state.mail.type' => 'required',
            ],
            [
                'state.mail.type.required' => 'Select a Mailing Type',
            ]
        );
        if ($this->state['mail']['type'] == 'smtp') {
            $this->validate(
                [
                    'state.mail.smtp.host' => 'required',
                    'state.mail.smtp.port' => 'required',
                    'state.mail.smtp.username' => 'required',
                    'state.mail.smtp.password' => 'required',
                    'state.mail.smtp.encryption' => 'required',
                    'state.mail.from.address' => 'required',
                    'state.mail.from.name' => 'required',
                ],
                [
                    'state.mail.smtp.host.required' => 'Site Key is Required',
                    'state.mail.smtp.port.required' => 'Secret Key is Required',
                    'state.mail.smtp.username.required' => 'Username is Required',
                    'state.mail.smtp.password.required' => 'Password is Required',
                    'state.mail.smtp.encryption.required' => 'Encryption Type is Required',
                    'state.mail.from.address.required' => 'From Address is Required',
                    'state.mail.from.name.required' => 'From Name is Required',
                ]
            );
        }
        $data = [
            'MAIL_MAILER' => $this->state['mail']['type'],
            'MAIL_HOST' => $this->state['mail']['smtp']['host'],
            'MAIL_PORT' => $this->state['mail']['smtp']['port'],
            'MAIL_USERNAME' => $this->state['mail']['smtp']['username'],
            'MAIL_PASSWORD' => $this->state['mail']['smtp']['password'],
            'MAIL_ENCRYPTION' => $this->state['mail']['smtp']['encryption'],
            'MAIL_FROM_ADDRESS' => $this->state['mail']['from']['address'],
            'MAIL_FROM_NAME' => $this->state['mail']['from']['name'],
        ];
        $this->changeEnv($data);
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.mail');
    }

    private function changeEnv($data = array()) {
        if (count($data) > 0) {
            $env = file_get_contents(base_path() . '/.env');
            $env = explode("\n", $env);
            foreach ((array)$data as $key => $value) {
                if ($key == "_token") {
                    continue;
                }
                $notfound = true;
                foreach ($env as $env_key => $env_value) {
                    $entry = explode("=", $env_value, 2);
                    if ($entry[0] == $key) {
                        $env[$env_key] = $key . "=\"" . $value . "\"";
                        $notfound = false;
                    } else {
                        $env[$env_key] = $env_value;
                    }
                }
                if ($notfound) {
                    $env[$env_key + 1] = "\n" . $key . "=\"" . $value . "\"";
                }
            }
            $env = implode("\n", $env);
            file_put_contents(base_path() . '/.env', $env);
            return true;
        } else {
            return false;
        }
    }
}
