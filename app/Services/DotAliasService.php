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
     * Generate a dotted variation based on a numeric index.
     * This allows deterministic generation without repetition.
     */
    public function generate(int $index): string
    {
        $length = strlen($this->username);

        // Maximum combinations = 2^(length - 1)
        $max = pow(2, $length - 1);

        if ($index >= $max) {
            throw new \Exception('Dot alias combinations exhausted.');
        }

        $binary = str_pad(decbin($index), $length - 1, '0', STR_PAD_LEFT);

        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $this->username[$i];

            if ($i < $length - 1 && $binary[$i] === '1') {
                $result .= '.';
            }
        }

        return $result . '@' . $this->domain;
    }

    /**
     * Get maximum possible dot variations.
     */
    public function max(): int
    {
        $length = strlen($this->username);
        return pow(2, $length - 1);
    }
}
