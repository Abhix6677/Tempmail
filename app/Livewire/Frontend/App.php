<?php

namespace App\Livewire\Frontend;

use App\Models\Message;
use Livewire\Component;
use App\Services\TMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $this->email = TMail::getEmail(true);
        $this->initial = false;
        if ($this->email) {
            $cached = Cache::get('tmail_inbox_' . md5($this->email))
                ?: session()->get('tmail_messages_' . $this->email, []);
            if (!empty($cached)) {
                $this->messages = $cached;
                $this->initial = true;
            }
        }
    }

    #[On('syncEmail')]
    public function syncEmail($email) {
        if ($this->email === $email && !empty($this->messages)) {
            return;
        }
        $this->email = $email;
        if ($this->email) {
            $cached = Cache::get('tmail_inbox_' . md5($this->email))
                ?: session()->get('tmail_messages_' . $this->email, []);
            if (!empty($cached)) {
                $this->messages = $cached;
                $this->initial = true;
            } else {
                $this->messages = [];
                $this->initial = false;
            }
        }
    }

    #[On('fetchMessages')]
    public function fetch($force = false) {
        $this->error = '';
        $this->errorDetails = '';

        if (!$this->email) {
            $this->dispatch('stopLoader');
            $this->dispatch('fetchCompleted');
            $this->initial = true;
            return;
        }

        // Smart throttle: if messages were fetched within the last 15 seconds,
        // don't block the PHP thread with another 4-second IMAP round-trip unless $force is true
        $cacheKey = 'tmail_last_fetch_' . md5($this->email);
        $lastFetch = Cache::get($cacheKey);
        if (!$force && $lastFetch && (microtime(true) - $lastFetch) < 15) {
            $cached = Cache::get('tmail_inbox_' . md5($this->email), []);
            if (!empty($cached)) {
                $this->messages = $cached;
            }
            $this->dispatch('stopLoader');
            $this->dispatch('fetchCompleted');
            $this->initial = true;
            return;
        }

        // CRITICAL: Release the session lock immediately before starting slow IMAP connection.
        // In PHP with file sessions, an active session holds an exclusive flock on the session file.
        // Releasing it here allows the user to click any route/page without waiting for IMAP!
        if (session()->isStarted()) {
            session()->save();
        }

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
            if ($this->email) {
                Cache::put('tmail_inbox_' . md5($this->email), $this->messages, 3600);
                Cache::put($cacheKey, microtime(true), 60);
            }
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
            $this->dispatch('fetchCompleted');
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
        $this->fetch(true);
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
        $this->messages = array_values($this->messages);
        if ($this->email) {
            Cache::put('tmail_inbox_' . md5($this->email), $this->messages, 3600);
            Cache::forget('tmail_last_fetch_' . md5($this->email));
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
