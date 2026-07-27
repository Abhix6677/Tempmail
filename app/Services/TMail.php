<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Log;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Models\Stat;
use Carbon\Carbon;
use App\Services\SocketImap\SocketConnection;
use App\Services\SocketImap\SocketMailbox;
use App\Services\SocketImap\SocketSearchExpression;
use App\Services\SocketImap\SocketSearchTo;
use App\Services\SocketImap\SocketSearchCc;
use App\Services\SocketImap\SocketSearchSince;

class TMail extends Model {
    /**
     * Session key constants
     */
    private const SESSION_EMAIL = 'email';
    private const SESSION_EMAILS = 'emails';

    /**
     * Check if the php-imap C extension is available.
     */
    public static function hasImapExtension(): bool
    {
        return extension_loaded('imap');
    }

    /**
     * Request-level connection cache: one IMAP connection per request.
     * Prevents re-authenticating for every getMessages() call (to + cc).
     */
    private static $connectionCache = null;

    public static function connectMailBox($imap = null) {
        /**
         * Connect to IMAP mailbox.
         * Uses the php-imap extension when available, otherwise falls back
         * to a pure-PHP socket-based IMAP client.
         *
         * Connection is cached per request via a static variable so it is
         * reused for both 'to' and 'cc' fetches within a single fetch cycle.
         *
         * @param array|null $imap
         * @return \Ddeboer\Imap\Connection|SocketConnection
         */

        // Return cached connection if available (same request, reused across to/cc calls)
        if (self::$connectionCache !== null) {
            \Log::debug('IMAP Connection reused from cache');
            return self::$connectionCache;
        }

        // Extend PHP execution time for IMAP operations (default 30s is too short for slow IMAP servers)
        set_time_limit(120);

        // Reduce socket timeout to prevent long freeze
        ini_set('default_socket_timeout', 10);

        $imap = $imap ?? config('app.settings.imap');

        if (!is_array($imap)) {
            throw new \Exception('IMAP settings are not configured or invalid.');
        }

        $required = ['host', 'port', 'username', 'password', 'protocol', 'encryption'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $imap) || $imap[$key] === '' || $imap[$key] === null) {
                throw new \Exception("IMAP configuration missing required field: {$key}");
            }
        }

        // Trim credentials to avoid hidden spaces causing Outlook auth failures
        $username = trim($imap['username']);
        $password = trim($imap['password']);

        \Log::info('IMAP Connection Attempt', [
            'host' => $imap['host'],
            'port' => $imap['port'],
            'protocol' => $imap['protocol'],
            'encryption' => $imap['encryption'],
            'validate_cert' => $imap['validate_cert'] ?? null,
            'username' => $username,
            'password' => '******',
            'method' => self::hasImapExtension() ? 'php-imap' : 'socket'
        ]);

        // Use php-imap extension if available (original path)
        if (self::hasImapExtension()) {
            // Limit IMAP extension timeouts (prevents 30s hang on Gmail/Outlook)
            if (function_exists('imap_timeout')) {
                imap_timeout(IMAP_OPENTIMEOUT, 10);
                imap_timeout(IMAP_READTIMEOUT, 15);
                imap_timeout(IMAP_WRITETIMEOUT, 10);
                imap_timeout(IMAP_CLOSETIMEOUT, 5);
            }

            try {
                $flags = $imap['protocol'] . '/' . $imap['encryption'];
                $flags .= !empty($imap['validate_cert']) ? '/validate-cert' : '/novalidate-cert';

                $server = new \Ddeboer\Imap\Server($imap['host'], (int) $imap['port'], $flags);
                $connection = $server->authenticate($username, $password);
                self::$connectionCache = $connection;
                return $connection;
            } catch (\Throwable $e) {
                \Log::error('IMAP Connection Failed (php-imap)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                self::$connectionCache = null;
                throw new \Exception('IMAP connection failed: ' . $e->getMessage());
            }
        }

        // Fallback: pure PHP socket-based IMAP client (no extension needed)
        try {
            $flags = $imap['protocol'] . '/' . $imap['encryption'];
            $flags .= !empty($imap['validate_cert']) ? '/validate-cert' : '/novalidate-cert';

            $conn = new SocketConnection($imap['host'], (int) $imap['port'], $flags);
            $conn->connect();
            $conn->authenticate($username, $password);
            self::$connectionCache = $conn;
            return $conn;
        } catch (\Throwable $e) {
            \Log::error('IMAP Connection Failed (socket fallback)', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            self::$connectionCache = null;
            throw new \Exception('IMAP connection failed: ' . $e->getMessage());
        }
    }

    public static function getMessages($email, $type = 'to', $deleted = []) {
        /**
         * Get messages for an email
         * @param string $email
         * @param string $type
         * @param array $deleted
         * @return array
         */
        if (config('app.settings.engine') === 'delivery') {
            return Message::getMessages($email);
        }
        $connection = self::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');

        // Build search expression based on connection type
        $useSocket = $connection instanceof SocketConnection;

        if ($useSocket) {
            $search = new SocketSearchExpression();
            $isGmailAlias = str_ends_with(strtolower(trim($email)), '@gmail.com');

            if (!$isGmailAlias) {
                $search->addCondition($type === 'cc' ? new SocketSearchCc($email) : new SocketSearchTo($email));
            }

            // Only fetch messages received AFTER email was assigned
            if (session()->has('email_start_time')) {
                /** @var \Carbon\Carbon|null $sinceDate */
                $sinceDate = session('email_start_time');
                if ($sinceDate instanceof \DateTimeInterface) {
                    $search->addCondition(new SocketSearchSince($sinceDate));
                }
            }

            // Hard cutoff: never show messages older than 10 minutes
            $tenMinAgo = Carbon::now()->subMinutes(10)->startOfDay();
            $search->addCondition(new SocketSearchSince($tenMinAgo));
        } else {
            $search = new \Ddeboer\Imap\SearchExpression();
            $search->addCondition($type === 'cc'
                ? new \Ddeboer\Imap\Search\Email\Cc($email)
                : new \Ddeboer\Imap\Search\Email\To($email));

            // Only fetch messages received AFTER email was assigned
            if (session()->has('email_start_time')) {
                /** @var \Carbon\Carbon|null $sinceDate */
                $sinceDate = session('email_start_time');
                if ($sinceDate instanceof \DateTimeInterface) {
                    $search->addCondition(new \Ddeboer\Imap\Search\Date\Since($sinceDate));
                }
            }

            // Hard cutoff: never show messages older than 10 minutes
            $tenMinAgo = Carbon::now()->subMinutes(10)->startOfDay();
            $search->addCondition(new \Ddeboer\Imap\Search\Date\Since($tenMinAgo));
        }

        $messages = $useSocket
            ? $mailbox->getMessages($search, 'SORTDATE', true)
            : $mailbox->getMessages($search, \SORTDATE, true);
        $limit = (int) config('app.settings.fetch_messages_limit');
        $response = ['data' => [], 'notifications' => []];
        $count = 0;
        $cutoff = Carbon::now()->subMinutes(10);

        foreach ($messages as $message) {
            if (in_array($message->getNumber(), $deleted, true)) {
                $message->delete();
                continue;
            }

            // Skip messages older than 10 minutes (minute-level precision)
            $msgDate = $message->getDate();
            if ($msgDate && Carbon::parse($msgDate)->lt($cutoff)) {
                continue;
            }

            if ($useSocket && method_exists($message, 'matchesRecipient') && !$message->matchesRecipient($email, $type)) {
                continue;
            }

            $data = self::formatMessage($message, $email);
            $response['data'][] = $data['message'];
            if ($data['notification']) {
                $response['notifications'][] = $data['notification'];
            }
            if (++$count >= $limit) break;
        }
        try {
            $connection->expunge();
        } catch (\Throwable $e) {
            \Log::warning('IMAP expunge failed (non-fatal): ' . $e->getMessage());
        }
        return $response;
    }

    public static function formatMessage($message, $email = null) {
        /**
         * Format a message object
         * @param object $message
         * @param string|null $email
         * @return array
         */
        $file_types = config('app.settings.allowed_file_types', 'csv,doc,docx,xls,xlsx,ppt,pptx,xps,pdf,dxf,ai,psd,eps,ps,svg,ttf,zip,rar,tar,gzip,mp3,mpeg,wav,ogg,jpeg,jpg,png,gif,bmp,tif,webm,mpeg4,3gpp,mov,avi,mpegs,wmv,flx,txt');
        $allowed = array_map('strtolower', array_map('trim', explode(',', $file_types)));
        $sender = $message->getFrom();
        $date = $message->getDate() ?: (new \DateTime());
        if (!$message->getDate() && $message->getHeaders()->get('udate')) {
            $date->setTimestamp($message->getHeaders()->get('udate'));
        }
        $datediff = new Carbon($date);
        $html = $message->getBodyHtml();
        $text = $message->getBodyText();
        $content = $html ? str_replace('<a', '<a target="blank"', $html)
            : str_replace('<a', '<a target="blank"', str_replace(["\r\n", "\n"], '<br/>', $text));
        $masker = config('app.settings.external_link_masker', '');
        if ($masker) {
            $content = str_replace('href="', 'href="' . $masker . '/?', $content);
        }
        $obj = [
            'subject' => $message->getSubject(),
            'sender_name' => $sender->getName(),
            'sender_email' => $sender->getAddress(),
            'timestamp' => $message->getDate(),
            'date' => $date->format(config('app.settings.date_format', 'd M Y h:i A')),
            'datediff' => $datediff->diffForHumans(),
            'id' => $message->getNumber(),
            'content' => $content,
            'attachments' => []
        ];
        // Blocked sender check
        $domain = explode('@', $obj['sender_email'])[1] ?? '';
        $blocked = in_array($domain, config('app.settings.blocked_domains', []), true);
        if ($blocked) {
            $obj['subject'] = __('Blocked');
            $obj['content'] = __('Emails from') . ' ' . $domain . ' ' . __('are blocked by Admin');
        }
        // Check if in Allowed Domains
        if (config('app.settings.allowed_domains', [])) {
            $allowed = !in_array($domain, config('app.settings.allowed_domains', []), true);
            if ($allowed) {
                $obj['subject'] = __('Blocked');
                $obj['content'] = __('Emails from') . ' ' . $domain . ' ' . __('are blocked by Admin');
            }
        }
        // Attachments
        if ($message->hasAttachments() && !$blocked) {
            $attachments = $message->getAttachments();
            $directory = './tmp/attachments/' . $obj['id'] . '/';
            if (!is_dir($directory)) mkdir($directory, 0777, true);
            foreach ($attachments as $attachment) {
                $filename = $attachment->getFilename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($extension, $allowed, true)) {
                    $filepath = $directory . $filename;
                    if (!file_exists($filepath)) {
                        file_put_contents($filepath, $attachment->getDecodedContent());
                    }
                    if ($filename !== 'undefined') {
                        $url = env('APP_URL') . str_replace('./', '/', $filepath);
                        $structure = $attachment->getStructure();
                        if (isset($structure->id) && str_contains($obj['content'], trim($structure->id, '<>'))) {
                            $obj['content'] = str_replace('cid:' . trim($structure->id, '<>'), $url, $obj['content']);
                        }
                        $obj['attachments'][] = ['file' => $filename, 'url' => $url];
                    }
                }
            }
        }
        // Notification
        $notification = '';
        if (!$message->isSeen()) {
            $notification = [
                'subject' => $obj['subject'],
                'sender_name' => $obj['sender_name'],
                'sender_email' => $obj['sender_email']
            ];
            if (env('ENABLE_TMAIL_LOGS', true) && $email) {
                file_put_contents(storage_path('logs/tmail.csv'), request()->ip() . "," . date("Y-m-d h:i:s a") . "," . $obj['sender_email'] . "," . $email . PHP_EOL, FILE_APPEND);
            }
        }
        $message->markAsSeen();
        return ['message' => $obj, 'notification' => $notification];
    }

    public static function deleteMessage($id) {
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $mailbox->getMessage($id)->delete();
        try {
            $connection->expunge();
        } catch (\Throwable $e) {
            \Log::warning('IMAP expunge failed on delete (non-fatal): ' . $e->getMessage());
        }
    }

    public static function getEmail($generate = false) {
        /**
         * Get current email from session or generate new
         * @param bool $generate
         * @return string|null
         */
        if (Session::has(self::SESSION_EMAIL)) {
            return Session::get(self::SESSION_EMAIL);
        }

        if ($generate) {
            return self::generateDotAliasEmail();
        }

        return null;
    }
    public static function getEmails() {
        /**
         * Get all emails from session
         * @return array
         */
        if (Session::has(self::SESSION_EMAILS)) {
            $emails = json_decode(Session::get(self::SESSION_EMAILS), true);
            return is_array($emails) ? $emails : [];
        }
        return [];
    }
    public static function setEmail($email) {
        /**
         * Set current email in session
         * @param string $email
         */
        $emails = self::getEmails();
        if (in_array($email, $emails, true)) {
            Session::put(self::SESSION_EMAIL, $email);
        }
    }
    public static function removeEmail($email) {
        /**
         * Remove email from session
         * @param string $email
         */
        $emails = self::getEmails();
        $key = array_search($email, $emails, true);
        if ($key !== false) {
            array_splice($emails, $key, 1);
        }
        if ($emails) {
            self::setEmail($emails[0]);
            Session::put(self::SESSION_EMAILS, json_encode($emails));
        } else {
            Session::forget(self::SESSION_EMAIL);
            Session::forget(self::SESSION_EMAILS);
        }
    }

    /**
     * this method is used to save emails
     */

    private static function storeEmail($email) {
        /**
         * Store email in session and log
         * @param string $email
         */
        Log::create([
            'ip' => request()->ip(),
            'email' => $email
        ]);
        Session::put(self::SESSION_EMAIL, $email);
        $emails = self::getEmails();
        if (!in_array($email, $emails, true)) {
            self::incrementEmailStats();
            $emails[] = $email;
            Session::put(self::SESSION_EMAILS, json_encode($emails));
        }
    }
    public static function createCustomEmailFull($email) {
        /**
         * Create custom email with full address
         * @param string $email
         * @return string
         */
        [$username, $domain] = explode('@', $email);
        $min = (int) config('app.settings.custom.min');
        $max = (int) config('app.settings.custom.max');
        if (strlen($username) < $min || strlen($username) > $max) {
            $username = (new self)->generateRandomUsername();
        }
        return self::createCustomEmail($username, $domain);
    }

    public static function createCustomEmail($username, $domain) {
        /**
         * Create custom email
         * @param string $username
         * @param string $domain
         * @return string
         */
        $username = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($username));
        $forbidden_ids = config('app.settings.forbidden_ids', []);
        $domains = Domain::getDomainsForCurrentUser();
        if (in_array($username, $forbidden_ids, true)) {
            return self::generateDotAliasEmail();
        }
        $domain = in_array($domain, $domains, true) ? $domain : ($domains[0] ?? '');
        $email = $username . '@' . $domain;
        self::storeEmail($email);
        return $email;
    }

    /**
     * Stats Handling Functions
     */
    public static function incrementEmailStats($count = 1) {
        Stat::storeEmailsCreated($count);
    }

    public static function incrementMessagesStats($count = 1) {
        Stat::storeMessagesReceived($count);
    }

    public static function generateRandomEmail($store = true) {
        /**
         * Generate random email
         * @param bool $store
         * @return string
         */
        $tmail = new self;
        $domain = $tmail->getRandomDomain();
        
        // If no domains configured, try to use first available domain or fallback
        if (empty($domain)) {
            $allDomains = Domain::pluck('domain')->toArray();
            $domain = $allDomains[0] ?? 'example.com';
        }
        
        $email = $tmail->generateRandomUsername() . '@' . $domain;
        if ($store) {
            self::storeEmail($email);
        }
        return $email;
    }

    /**
     * Generate a random Gmail dot-alias for the configured IMAP account.
     */
    public static function generateDotAliasEmail(bool $store = true): string
    {
        $imapUser = config('app.settings.imap.username');

        if (!$imapUser || !str_contains($imapUser, '@') || $imapUser === 'username') {
            return self::generateRandomEmail($store);
        }

        $email = (new \App\Services\DotAliasService($imapUser))->generate();

        if ($store) {
            self::storeEmail($email);
        }

        return $email;
    }

    private function generateRandomUsername() {
        $start = config('app.settings.random.start', 0);
        $end = config('app.settings.random.end', 0);
        if ($start == 0 && $end == 0) {
            return $this->generatePronounceableWord();
        }
        return $this->generatedRandomBetweenLength($start, $end);
    }

    protected function generatedRandomBetweenLength($start, $end) {
        $length = rand($start, $end);
        return $this->generateRandomString($length);
    }

    private function getRandomDomain() {
        $domains = Domain::getDomainsForCurrentUser();
        $count = count($domains);
        return $count > 0 ? $domains[rand(0, $count - 1)] : '';
    }

    private function generatePronounceableWord() {
        $c  = 'bcdfghjklmnprstvwz'; // consonants
        $v  = 'aeiou';              // vowels
        $a  = $c . $v;              // both
        $random = '';
        for ($j = 0; $j < 2; $j++) {
            $random .= $c[rand(0, strlen($c) - 1)];
            $random .= $v[rand(0, strlen($v) - 1)];
            $random .= $a[rand(0, strlen($a) - 1)];
        }
        return $random;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
