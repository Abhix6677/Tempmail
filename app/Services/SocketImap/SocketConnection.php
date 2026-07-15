<?php

namespace App\Services\SocketImap;

/**
 * Pure PHP IMAP client using fsockopen/stream_socket_client.
 * Used as a fallback when the php-imap C extension is not available.
 */
class SocketConnection
{
    private $stream = null;
    private string $host;
    private int $port;
    private string $flags;
    private int $tagIndex = 0;
    private bool $authenticated = false;

    public function __construct(string $host, int $port, string $flags = 'imap/ssl')
    {
        $this->host = $host;
        $this->port = $port;
        $this->flags = $flags;
    }

    public function connect(): void
    {
        $protocol = stripos($this->flags, 'ssl') !== false ? 'ssl' : 'tcp';
        $address = "{$protocol}://{$this->host}:{$this->port}";

        $errno = 0;
        $errstr = '';
        $this->stream = @stream_socket_client($address, $errno, $errstr, 10);

        if (!$this->stream) {
            throw new \RuntimeException("IMAP connection failed: {$errstr} ({$errno})");
        }

        // Read server greeting
        $greeting = $this->readLine();
        if (strpos($greeting, 'OK') === false && strpos($greeting, 'PREAUTH') === false) {
            throw new \RuntimeException("Unexpected IMAP greeting: {$greeting}");
        }

        // If PREAUTH, we're already authenticated
        if (strpos($greeting, 'PREAUTH') !== false) {
            $this->authenticated = true;
        }
    }

    public function authenticate(string $username, string $password): void
    {
        if ($this->authenticated) {
            return;
        }

        $tag = $this->nextTag();
        $this->sendCommand("{$tag} LOGIN {$this->quoteString($username)} {$this->quoteString($password)}");
        $this->readUntilTag($tag, true);
        $this->authenticated = true;
    }

    public function getMailbox(string $name): SocketMailbox
    {
        $tag = $this->nextTag();
        $this->sendCommand("{$tag} SELECT {$name}");
        $this->readUntilTag($tag, true);
        return new SocketMailbox($this, $name);
    }

    public function expunge(): void
    {
        $tag = $this->nextTag();
        $this->sendCommand("{$tag} EXPUNGE");
        $this->readUntilTag($tag);
    }

    public function logout(): void
    {
        try {
            $tag = $this->nextTag();
            $this->sendCommand("{$tag} LOGOUT");
            $this->readUntilTag($tag);
        } catch (\Throwable $e) {
            // Ignore errors on logout
        }
        if ($this->stream) {
            fclose($this->stream);
            $this->stream = null;
        }
    }

    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Send a raw IMAP command and return the full response lines.
     */
    public function sendAndRead(string $command): array
    {
        $tag = $this->nextTag();
        $fullCommand = "{$tag} {$command}";
        $this->sendCommand($fullCommand);
        return $this->readUntilTag($tag);
    }

    /**
     * Send a FETCH command and return the response lines.
     */
    public function fetch(string $sequence, string $items): array
    {
        $tag = $this->nextTag();
        $this->sendCommand("{$tag} FETCH {$sequence} {$items}");
        return $this->readUntilTag($tag);
    }

    /**
     * Store flags on a message.
     */
    public function storeFlags(string $sequence, string $action, array $flags): void
    {
        $flagStr = implode(' ', $flags);
        $tag = $this->nextTag();
        $this->sendCommand("{$tag} STORE {$sequence} {$action} ({$flagStr})");
        $this->readUntilTag($tag);
    }

    /**
     * Search messages with a raw IMAP SEARCH command.
     */
    public function search(string $criteria): array
    {
        $tag = $this->nextTag();
        $this->sendCommand("{$tag} SEARCH {$criteria}");
        $lines = $this->readUntilTag($tag);

        // Parse SEARCH response: "* SEARCH 1 2 3"
        foreach ($lines as $line) {
            if (preg_match('/^\* SEARCH (.+)$/i', trim($line), $m)) {
                $ids = trim($m[1]);
                if (empty($ids)) {
                    return [];
                }
                return array_map('intval', preg_split('/\s+/', $ids));
            }
        }
        return [];
    }

    /**
     * Get the underlying stream resource.
     */
    public function getStream()
    {
        return $this->stream;
    }

    // ---- Private helpers ----

    private function nextTag(): string
    {
        return 'a' . (++$this->tagIndex);
    }

    private function sendCommand(string $command): void
    {
        if (!$this->stream) {
            throw new \RuntimeException('Not connected to IMAP server');
        }
        fwrite($this->stream, $command . "\r\n");
    }

    private function readLine(): string
    {
        if (!$this->stream) {
            throw new \RuntimeException('Not connected to IMAP server');
        }
        $line = fgets($this->stream, 8192);
        if ($line === false) {
            throw new \RuntimeException('Failed to read from IMAP server');
        }
        return rtrim($line, "\r\n");
    }

    /**
     * Read lines until we get a tagged response.
     * Returns all lines including the tagged one.
     */
    private function readUntilTag(string $tag, bool $throwOnNo = false): array
    {
        $lines = [];

        while (true) {
            $line = $this->readLine();
            $lines[] = $line;

            // Handle literal strings {size} — IMAP sends data blocks like:
            //   "* 1 FETCH (RFC822 {1234}\r\n"
            //   <1234 bytes of raw email data>\r\n"
            //   ")\r\n"
            if (preg_match('/\{(\d+)\}\s*$/', $line, $m)) {
                $literalSize = (int)$m[1];
                // Read exactly $literalSize bytes of literal data
                $literalData = '';
                $remaining = $literalSize;
                while ($remaining > 0) {
                    $chunk = fread($this->stream, min(8192, $remaining));
                    if ($chunk === false || $chunk === '') {
                        // Timeout or stream closed
                        break;
                    }
                    $literalData .= $chunk;
                    $remaining -= strlen($chunk);
                }
                // Read the CRLF that terminates the literal
                fgets($this->stream, 1024);
                // Append the literal data as the next element in lines
                // so callers can extract it (e.g. for FETCH RFC822)
                $lines[] = $literalData;
                continue;
            }

            // Check if this is our tagged response
            if (strpos($line, $tag . ' ') === 0) {
                if ($throwOnNo) {
                    if (preg_match('/^' . preg_quote($tag) . '\s+(NO|BAD)\s/i', $line)) {
                        throw new \RuntimeException("IMAP command failed: {$line}");
                    }
                }
                break;
            }
        }

        return $lines;
    }

    private function quoteString(string $str): string
    {
        // If the string contains special chars, quote it
        if (preg_match('/[\s"\\\\]/', $str)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $str) . '"';
        }
        return $str;
    }
}
