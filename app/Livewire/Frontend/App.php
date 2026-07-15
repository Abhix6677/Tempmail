<?php

namespace App\Livewire\Frontend;

use App\Models\Message;
use Livewire\Component;
use App\Services\TMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class App extends Component {

    public $messages = [];
    public $deleted = [];
    public $error = '';
    public $email;
    public $initial;
    public $overflow = false;

    protected $listeners = ['fetchMessages' => 'fetch', 'syncEmail', 'emailGenerated'];

    public function mount() {
        $this->email = TMail::getEmail();
        $this->initial = false;
    }

    public function syncEmail($email) {
        $this->email = $email;
    }

    /**
     * Called when a new email is generated (via create/random/deleteEmail)
     * Clears stale messages so the UI doesn't show old email's inbox.
     */
    public function emailGenerated($email) {
        $this->email = $email;
        $this->messages = [];
        $this->deleted = [];
        $this->initial = false;
        $this->overflow = false;
        $this->error = '';
    }

    public function fetch() {
        $this->dispatch('fetchStarted');

        try {
            // Skip IMAP fetch if email is empty (no point connecting)
            if (!$this->email) {
                $this->initial = true;
                $this->dispatch('stopLoader');
                $this->dispatch('loadDownload');
                $this->dispatch('fetchCompleted');
                return;
            }

            $count = count($this->messages);
            $responses = [];
            if (config('app.settings.engine') == 'delivery' || !config('app.settings.imap.cc_check', false)) {
                $responses = [
                    'to' => TMail::getMessages($this->email, 'to', $this->deleted),
                    'cc' => [
                        'data' => [],
                        'notifications' => []
                    ]
                ];
            } else {
                $responses = [
                    'to' => TMail::getMessages($this->email, 'to', $this->deleted),
                    'cc' => TMail::getMessages($this->email, 'cc', $this->deleted)
                ];
            }
            $this->deleted = [];
            $this->messages = array_merge($responses['to']['data'], $responses['cc']['data']);
            $notifications = array_merge($responses['to']['notifications'], $responses['cc']['notifications']);
            if (count($notifications)) {
                if ($this->overflow == false && count($this->messages) == $count) {
                    $this->overflow = true;
                }
            } else {
                $this->overflow = false;
            }
            foreach ($notifications as $notification) {
                $this->dispatch('showNewMailNotification', $notification);
            }
            if (config('app.settings.engine') != 'delivery') {
                TMail::incrementMessagesStats(count($notifications));
            }
        } catch (\Throwable $e) {
            // Log the full error for debugging
            \Illuminate\Support\Facades\Log::warning('TMail fetch failed', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            // Surface the real error to admins, a user-friendly message to everyone
            if (Auth::check() && Auth::user()->role == 7) {
                $this->error = __('IMAP Error') . ': ' . $e->getMessage();
            } else {
                $this->error = __('Not able to connect to Mail Server');
            }
        }
        $this->dispatch('stopLoader');
        $this->dispatch('loadDownload');
        $this->initial = true;
        $this->dispatch('fetchCompleted');
    }

    public function delete($messageId) {
        if (config('app.settings.engine') == 'delivery') {
            Message::find($messageId)->delete();
        }
        array_push($this->deleted, $messageId);
        foreach ($this->messages as $key => $message) {
            if ($message['id'] == $messageId) {
                $directory = './tmp/attachments/' . $messageId;
                $this->rrmdir($directory);
                unset($this->messages[$key]);
            }
        }
    }

    public function render() {
        $theme = config('app.settings.theme') ?: 'default';

        if (!view()->exists("frontend.themes.$theme.components.app")) {
            $theme = 'default';
        }

        return view("frontend.themes.$theme.components.app");
    }

    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }
}
