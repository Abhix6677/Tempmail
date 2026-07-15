<?php

namespace App\Services\SocketImap;

/**
 * Socket-based IMAP address wrapper.
 * Provides a compatible interface with Ddeboer\Imap\Message\EmailAddress.
 */
class SocketAddress
{
    private string $name;
    private string $address;

    public function __construct(string $name, string $address)
    {
        $this->name = $name;
        $this->address = $address;
    }

    /**
     * Get the display name (e.g. "John Doe").
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the email address (e.g. "john@example.com").
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Parse a raw From header string into a SocketAddress.
     * Handles formats like:
     *   "John Doe <john@example.com>"
     *   "john@example.com"
     *   "=?UTF-8?B?...?=<john@example.com>"
     */
    public static function parseFromString(string $raw): self
    {
        $raw = trim($raw);

        if (empty($raw)) {
            return new self('Unknown', 'unknown@example.com');
        }

        // Try to match "Name <email>" format
        if (preg_match('/^(.*?)\s*<(.+?)>$/', $raw, $matches)) {
            $name = trim($matches[1]);
            $email = trim($matches[2]);

            // Remove surrounding quotes from name
            $name = trim($name, '"\'');

            // Decode RFC 2047 encoded words in name
            $name = self::decodeEncodedWords($name);

            // If name is empty (e.g. "<john@example.com>"), use email as name
            if (empty($name)) {
                $name = $email;
            }

            return new self($name, $email);
        }

        // Try to match "email" format (no angle brackets)
        if (filter_var($raw, FILTER_VALIDATE_EMAIL)) {
            return new self($raw, $raw);
        }

        // Fallback: treat entire string as email
        return new self($raw, $raw);
    }

    /**
     * Decode RFC 2047 encoded words in a string.
     */
    private static function decodeEncodedWords(string $value): string
    {
        return preg_replace_callback(
            '/=\?([^?]+)\?([bBqQ])\?([^?]+)\?=/',
            function ($matches) {
                $charset = $matches[1];
                $encoding = strtolower($matches[2]);
                $encoded = $matches[3];

                if ($encoding === 'b') {
                    $decoded = base64_decode($encoded);
                } else {
                    $decoded = quoted_printable_decode(str_replace('_', ' ', '=' . $encoded));
                }

                if ($charset && $charset !== 'us-ascii' && $charset !== 'utf-8') {
                    return mb_convert_encoding($decoded, 'UTF-8', $charset);
                }
                return $decoded;
            },
            $value
        );
    }

    public function __toString(): string
    {
        return "{$this->name} <{$this->address}>";
    }
}
