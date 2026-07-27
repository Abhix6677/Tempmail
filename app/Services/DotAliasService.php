<?php

namespace App\Services;

class DotAliasService
{
    protected string $baseEmail;
    protected string $username;
    protected string $domain;

    public function __construct(string $baseEmail)
    {
        $this->baseEmail = strtolower(trim($baseEmail));

        [$this->username, $this->domain] = explode('@', $this->baseEmail);

        // Gmail ignores dots, so remove existing dots first
        $this->username = str_replace('.', '', $this->username);
    }

    /**
     * Generate a random dotted variation for the Gmail address.
     */
    public function generate(): string
    {
        $length = strlen($this->username);
        $binary = '';

        for ($i = 0; $i < $length - 1; $i++) {
            $binary .= (string) random_int(0, 1);
        }

        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $this->username[$i];

            if ($i < $length - 1 && $binary[$i] === '1') {
                $result .= '.';
            }
        }

        return $result . '@' . $this->domain;
    }

}
