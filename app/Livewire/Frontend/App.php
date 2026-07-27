<?php

namespace App\Livewire\Frontend;

use App\Models\Message;
use Livewire\Component;
use App\Services\TMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class App extends Component {

    public $messages = [];
    public $deleted = [];
    public $error = '';
    public $errorDetails = '';
    public $email;
    public $initial;
    public $overflow = false;
    public $retryCount = 0;

    public function mount() {
        $this->email = TMail::getEmail();
        $this->initial = false;
    }

    #[On('syncEmail')]
    public function syncEmail($email) {
        $this->email = $email;
    }

    #[On('fetchMessages')]
    public function fetch() {
        $this->error = '';
        $this->errorDetails = '';
        try {
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
            // Success - reset retry count
            $this->retryCount = 0;
        } catch (\Exception $e) {
            // Log the actual error for diagnostics
            Log::error('Mail fetch failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $this->email
            ]);
            $this->retryCount++;
            
            // Extract actionable error message
            $message = $e->getMessage();
            if (Auth::check() && Auth::user()->role == 7) {
                $this->errorDetails = $message;
            }
            
            // Map common errors to user-friendly messages
            if (str_contains($message, 'not configured')) {
                $this->error = __('Mail server is not configured');
            } elseif (str_contains($message, 'Authentication') || str_contains($message, 'authentication') || str_contains($message, 'auth')) {
                $this->error = __('Authentication failed — check credentials');
            } elseif (str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
                $this->error = __('Connection timed out — server unreachable');
            } elseif (str_contains($message, 'certificate') || str_contains($message, 'cert')) {
                $this->error = __('SSL certificate error');
            } elseif (str_contains($message, 'refused') || str_contains($message, 'ECONNREFUSED')) {
                $this->error = __('Connection refused — wrong port or server down');
            } elseif (str_contains($message, 'DNS') || str_contains($message, 'getaddrinfo')) {
                $this->error = __('DNS lookup failed — host not found');
            } else {
                $this->error = __('Not able to connect to Mail Server');
            }
        } finally {
            $this->dispatch('stopLoader');
            $this->dispatch('loadDownload');
            $this->initial = true;
        }
    }

    /**
     * Retry connection with exponential backoff
     */
    public function retry() {
        $this->error = '';
        $this->errorDetails = '';
        $this->dispatch('fetchMessages');
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
