<?php

namespace App\Services\SocketImap;

/**
 * Socket-based IMAP message wrapper.
 * Parses raw RFC822 email content and provides a compatible interface
 * with Ddeboer\Imap\Message for use in TMail.
 */
class SocketMessage
{
    private SocketConnection $connection;
    private int $sequenceNumber;
    private array $headers = [];
    private string $rawHeaders = '';
    private ?string $bodyHtml = null;
    private ?string $bodyText = null;
    private array $attachments = [];
    private bool $seen = false;
    private ?\DateTimeImmutable $date = null;
    private ?string $subject = null;
    private ?SocketAddress $from = null;
    private bool $parsed = false;

    public function __construct(SocketConnection $connection, int $sequenceNumber, string $rawData)
    {
        $this->connection = $connection;
        $this->sequenceNumber = $sequenceNumber;
        $this->rawHeaders = $rawData;
    }

    /**
     * Parse the raw email data into headers and body.
     * Uses PHP's built-in mail_parse_headers() if available,
     * otherwise falls back to manual parsing.
     */
    private function ensureParsed(): void
    {
        if ($this->parsed) {
            return;
        }
        $this->parsed = true;

        // TEMP DEBUG: Log raw header data for diagnosing "Unknown" sender / "(No Subject)" issue
        $debugLog = storage_path('logs/imap_parse_debug.log');
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($debugLog, "[{$timestamp}] === Parsing message #{$this->sequenceNumber} ===\n", FILE_APPEND);
        file_put_contents($debugLog, "[{$timestamp}] Raw headers length: " . strlen($this->rawHeaders) . "\n", FILE_APPEND);

        // Split headers from body
        $parts = preg_split('/\r?\n\r?\n/', $this->rawHeaders, 2);
        $headerBlock = $parts[0] ?? '';
        $bodyBlock = $parts[1] ?? '';

        file_put_contents($debugLog, "[{$timestamp}] Header block length: " . strlen($headerBlock) . "\n", FILE_APPEND);
        file_put_contents($debugLog, "[{$timestamp}] First 200 chars of header: " . substr($headerBlock, 0, 200) . "\n", FILE_APPEND);

        // Parse headers
        if (function_exists('mail_parse_headers')) {
            $this->headers = mail_parse_headers($headerBlock);
            file_put_contents($debugLog, "[{$timestamp}] Used mail_parse_headers\n", FILE_APPEND);
        } else {
            $this->headers = $this->manualParseHeaders($headerBlock);
            file_put_contents($debugLog, "[{$timestamp}] Used manualParseHeaders\n", FILE_APPEND);
        }

        file_put_contents($debugLog, "[{$timestamp}] Parsed headers: " . json_encode($this->headers) . "\n", FILE_APPEND);

        // Parse subject
        $this->subject = $this->decodeHeader($this->headers['subject'] ?? '(No Subject)');
        file_put_contents($debugLog, "[{$timestamp}] Raw subject key: " . ($this->headers['subject'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($debugLog, "[{$timestamp}] Decoded subject: {$this->subject}\n", FILE_APPEND);

        // Parse from
        $fromRaw = $this->headers['from'] ?? '';
        file_put_contents($debugLog, "[{$timestamp}] Raw from key: " . ($this->headers['from'] ?? 'MISSING') . "\n", FILE_APPEND);
        $this->from = SocketAddress::parseFromString($fromRaw);
        file_put_contents($debugLog, "[{$timestamp}] Parsed from: " . $this->from->getAddress() . " | " . $this->from->getName() . "\n", FILE_APPEND);

        // Parse date
        $dateStr = $this->headers['date'] ?? '';
        file_put_contents($debugLog, "[{$timestamp}] Raw date key: " . ($this->headers['date'] ?? 'MISSING') . "\n", FILE_APPEND);
        if ($dateStr) {
            try {
                $this->date = new \DateTimeImmutable($dateStr);
            } catch (\Throwable $e) {
                $this->date = new \DateTimeImmutable('now');
                file_put_contents($debugLog, "[{$timestamp}] Date parse failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        // Check if seen
        $flags = $this->headers['flags'] ?? '';
        $this->seen = stripos($flags, '\\Seen') !== false;

        // Parse body (MIME)
        file_put_contents($debugLog, "[{$timestamp}] Body block length: " . strlen($bodyBlock) . "\n", FILE_APPEND);
        $this->parseBody($bodyBlock, $this->headers);
        file_put_contents($debugLog, "[{$timestamp}] HTML body: " . (empty($this->bodyHtml) ? 'EMPTY' : 'SET') . "\n", FILE_APPEND);
        file_put_contents($debugLog, "[{$timestamp}] Text body: " . (empty($this->bodyText) ? 'EMPTY' : 'SET') . "\n\n", FILE_APPEND);
    }

    /**
     * Parse MIME body to extract HTML, text, and attachments.
     */
    private function parseBody(string $body, array $headers): void
    {
        $contentType = $this->decodeHeader($headers['content-type'] ?? 'text/plain');

        // Simple text email
        if (stripos($contentType, 'text/plain') !== false && stripos($contentType, 'multipart') === false) {
            $this->bodyText = $this->decodeBody($body, $headers['content-transfer-encoding'] ?? '');
            return;
        }

        if (stripos($contentType, 'text/html') !== false && stripos($contentType, 'multipart') === false) {
            $this->bodyHtml = $this->decodeBody($body, $headers['content-transfer-encoding'] ?? '');
            return;
        }

        // Multipart email
        if (stripos($contentType, 'multipart') !== false) {
            $boundary = $this->extractBoundary($contentType);
            if ($boundary) {
                $this->parseMultipart($body, $boundary);
                return;
            }
        }

        // Fallback: treat as text
        $this->bodyText = $this->decodeBody($body, $headers['content-transfer-encoding'] ?? '');
    }

    /**
     * Parse multipart MIME body.
     */
    private function parseMultipart(string $body, string $boundary): void
    {
        // Split by boundary
        $parts = preg_split('/--' . preg_quote($boundary, '/') . '(?:--)?\s*\r?\n/s', $body);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            // Split part headers from content
            $parts2 = preg_split('/\r?\n\r?\n/', $part, 2);
            $partHeaders = $parts2[0] ?? '';
            $partBody = $parts2[1] ?? '';

            // Parse part headers
            $parsedHeaders = function_exists('mail_parse_headers')
                ? mail_parse_headers($partHeaders)
                : $this->manualParseHeaders($partHeaders);

            $partContentType = $this->decodeHeader($parsedHeaders['content-type'] ?? 'text/plain');
            $partEncoding = $parsedHeaders['content-transfer-encoding'] ?? '';

            // Check if this is a nested multipart
            if (stripos($partContentType, 'multipart') !== false) {
                $nestedBoundary = $this->extractBoundary($partContentType);
                if ($nestedBoundary) {
                    $this->parseMultipart($partBody, $nestedBoundary);
                    continue;
                }
            }

            // Check if this is an attachment
            $contentDisposition = $parsedHeaders['content-disposition'] ?? '';
            $contentId = $parsedHeaders['content-id'] ?? '';

            if (stripos($contentDisposition, 'attachment') !== false || 
                (stripos($contentDisposition, 'inline') !== false && !empty($contentId))) {
                // It's an attachment or inline image
                $this->attachments[] = new SocketAttachment($partBody, $partHeaders, $parsedHeaders, $partEncoding);
                continue;
            }

            // It's a body part
            $decoded = $this->decodeBody($partBody, $partEncoding);

            if (stripos($partContentType, 'text/html') !== false) {
                $this->bodyHtml = $decoded;
            } elseif (stripos($partContentType, 'text/plain') !== false) {
                $this->bodyText = $decoded;
            } elseif (empty($this->bodyText)) {
                $this->bodyText = $decoded;
            }
        }
    }

    // ---- Public interface (matching ddeboer/imap Message) ----

    public function getNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function getSubject(): string
    {
        $this->ensureParsed();
        return $this->subject ?? '(No Subject)';
    }

    public function getFrom(): SocketAddress
    {
        $this->ensureParsed();
        return $this->from ?? new SocketAddress('unknown', 'unknown@example.com');
    }

    public function getDate(): ?\DateTimeImmutable
    {
        $this->ensureParsed();
        return $this->date;
    }

    public function getBodyHtml(): ?string
    {
        $this->ensureParsed();
        return $this->bodyHtml;
    }

    public function getBodyText(): ?string
    {
        $this->ensureParsed();
        return $this->bodyText;
    }

    public function hasAttachments(): bool
    {
        $this->ensureParsed();
        return count($this->attachments) > 0;
    }

    public function getAttachments(): array
    {
        $this->ensureParsed();
        return $this->attachments;
    }

    public function isSeen(): bool
    {
        return $this->seen;
    }

    public function markAsSeen(): void
    {
        $this->connection->storeFlags((string)$this->sequenceNumber, '+FLAGS', ['\\Seen']);
        $this->seen = true;
    }

    public function delete(): void
    {
        $this->connection->storeFlags((string)$this->sequenceNumber, '+FLAGS', ['\\Deleted']);
    }

    public function matchesRecipient(string $email, string $type = 'to'): bool
    {
        $this->ensureParsed();

        $email = strtolower(trim($email));
        $normalizedEmail = $this->normalizeGmailAddress($email);
        $headersToCheck = $type === 'cc'
            ? ['cc']
            : ['to', 'delivered-to', 'x-original-to', 'x-forwarded-to', 'envelope-to'];

        foreach ($headersToCheck as $header) {
            $value = strtolower((string) ($this->headers[$header] ?? ''));
            if ($value === '') {
                continue;
            }

            if (str_contains($value, $email) || $this->headerContainsNormalizedGmail($value, $normalizedEmail)) {
                return true;
            }
        }

        $raw = strtolower($this->rawHeaders);
        return str_contains($raw, $email) || $this->headerContainsNormalizedGmail($raw, $normalizedEmail);
    }

    private function headerContainsNormalizedGmail(string $value, string $normalizedEmail): bool
    {
        if (!str_ends_with($normalizedEmail, '@gmail.com')) {
            return false;
        }

        if (!preg_match_all('/[a-z0-9._%+\-]+@gmail\.com/i', $value, $matches)) {
            return false;
        }

        foreach ($matches[0] as $address) {
            if ($this->normalizeGmailAddress($address) === $normalizedEmail) {
                return true;
            }
        }

        return false;
    }

    private function normalizeGmailAddress(string $email): string
    {
        $email = strtolower(trim($email));
        if (!str_ends_with($email, '@gmail.com')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $local = explode('+', $local, 2)[0];
        return str_replace('.', '', $local) . '@' . $domain;
    }

    public function getHeaders()
    {
        $this->ensureParsed();
        return new class($this->headers) {
            private array $headers;
            public function __construct(array $headers) { $this->headers = $headers; }
            public function get(string $name) { return $this->headers[strtolower($name)] ?? null; }
        };
    }

    // ---- Private helpers ----

    private function manualParseHeaders(string $headerBlock): array
    {
        $headers = [];
        $lines = preg_split('/\r?\n/', $headerBlock);
        $currentKey = null;

        foreach ($lines as $line) {
            if (preg_match('/^(\S+?):\s*(.*)$/', $line, $m)) {
                $key = strtolower($m[1]);
                $headers[$key] = $m[2];
                $currentKey = $key;
            } elseif ($currentKey !== null && preg_match('/^\s+(.*)$/', $line, $m)) {
                $headers[$currentKey] .= ' ' . trim($m[1]);
            }
        }

        return $headers;
    }

    private function decodeHeader(string $value): string
    {
        // Decode RFC 2047 encoded words: =?charset?encoding?encoded_text?=
        return preg_replace_callback('/=\?([^?]+)\?([bBqQ])\?([^?]+)\?=/',
            function ($matches) {
                $charset = $matches[1];
                $encoding = strtolower($matches[2]);
                $encoded = $matches[3];

                if ($encoding === 'b') {
                    $decoded = base64_decode($encoded);
                } else {
                    // Q encoding: replace =XX with actual chars, _ with space
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

    private function decodeBody(string $body, string $encoding): string
    {
        $encoding = strtolower(trim($encoding));

        if ($encoding === 'base64') {
            return base64_decode($body) ?: $body;
        }

        if ($encoding === 'quoted-printable') {
            return quoted_printable_decode($body);
        }

        return $body;
    }

    private function extractBoundary(string $contentType): ?string
    {
        if (preg_match('/boundary=["\']?([^"\';\s]+)["\']?/i', $contentType, $m)) {
            return $m[1];
        }
        return null;
    }
}
