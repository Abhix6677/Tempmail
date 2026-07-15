<?php

namespace App\Services;

use App\Models\TempEmail;
use Carbon\Carbon;

class DotTempEmailService
{
    protected string $baseEmail;
    protected string $username;
    protected string $domain;

    public function __construct()
    {
        $imapUser = config('app.settings.imap.username');

        if (!$imapUser || !str_contains($imapUser, '@')) {
            throw new \Exception('Invalid IMAP base email configuration.');
        }

        $this->baseEmail = strtolower($imapUser);
        [$this->username, $this->domain] = explode('@', $this->baseEmail);

        // Remove existing dots from base username
        $this->username = str_replace('.', '', $this->username);
    }

    /**
     * Generate a unique dotted alias and store in DB
     */
    public function generate(): TempEmail
    {
        $maxAttempts = 50;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $alias = $this->generateRandomDotVariant();

            $exists = TempEmail::where('generated_address', $alias)
                ->where('is_active', true)
                ->where('expires_at', '>', Carbon::now())
                ->exists();

            if (!$exists) {
                return TempEmail::create([
                    'generated_address' => $alias,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'is_active' => true,
                ]);
            }
        }

        throw new \Exception('Unable to generate unique dot alias.');
    }

    /**
     * Insert random dots into base username
     */
    protected function generateRandomDotVariant(): string
    {
        $chars = str_split($this->username);
        $result = '';

        for ($i = 0; $i < count($chars); $i++) {
            $result .= $chars[$i];

            if ($i < count($chars) - 1) {
                // 50% chance to insert dot
                if (rand(0, 1) === 1) {
                    $result .= '.';
                }
            }
        }

        return $result . '@' . $this->domain;
    }
}
