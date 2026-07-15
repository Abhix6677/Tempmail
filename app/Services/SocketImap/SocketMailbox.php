<?php

namespace App\Services\SocketImap;

/**
 * Socket-based IMAP mailbox wrapper.
 * Provides a similar interface to Ddeboer\Imap\Mailbox for compatibility with TMail.
 */
class SocketMailbox
{
    private SocketConnection $connection;
    private string $name;

    public function __construct(SocketConnection $connection, string $name)
    {
        $this->connection = $connection;
        $this->name = $name;
    }

    /**
     * Get messages matching a search expression.
     * Returns an array of SocketMessage objects.
     *
     * @param SocketSearchExpression|null $search
     * @param string|null $sortField
     * @param bool $reverse
     * @return SocketMessage[]
     */
    public function getMessages(?SocketSearchExpression $search = null, ?string $sortField = null, bool $reverse = false): array
    {
        $criteria = $search ? $search->toImapString() : 'ALL';

        $messageIds = $this->connection->search($criteria);

        if (empty($messageIds)) {
            return [];
        }

        $messages = [];
        foreach ($messageIds as $id) {
            try {
                $rawHeaders = $this->fetchRawMessage($id);
                if ($rawHeaders) {
                    $messages[] = new SocketMessage($this->connection, $id, $rawHeaders);
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to fetch IMAP message {$id}: " . $e->getMessage());
                continue;
            }
        }

        // Sort by date if requested
        if ($sortField === 'SORTDATE' || $sortField === 'DATE') {
            usort($messages, function (SocketMessage $a, SocketMessage $b) {
                $dateA = $a->getDate() ? $a->getDate()->getTimestamp() : 0;
                $dateB = $b->getDate() ? $b->getDate()->getTimestamp() : 0;
                return $dateB - $dateA; // Newest first
            });
            if (!$reverse) {
                $messages = array_reverse($messages);
            }
        }

        return $messages;
    }

    /**
     * Get a specific message by sequence number.
     */
    public function getMessage(int $id): ?SocketMessage
    {
        try {
            $rawHeaders = $this->fetchRawMessage($id);
            if ($rawHeaders) {
                return new SocketMessage($this->connection, $id, $rawHeaders);
            }
        } catch (\Throwable $e) {
            \Log::warning("Failed to fetch IMAP message {$id}: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Fetch raw message data (headers + body) by sequence number.
     */
    private function fetchRawMessage(int $id): ?string
    {
        $lines = $this->connection->fetch((string)$id, '(RFC822)');

        // TEMP DEBUG: Log raw fetch response for diagnosing "Unknown" sender / "(No Subject)" issue
        $debugLog = storage_path('logs/imap_fetch_debug.log');
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($debugLog, "[{$timestamp}] FETCH lines count: " . count($lines) . " | ID: {$id}\n", FILE_APPEND);

        // SocketConnection::readUntilTag() now preserves literal data:
        // When it encounters "{size}", it reads the literal bytes and appends
        // them as the NEXT element in the lines array.
        //
        // So the response looks like:
        //   [0] "* N FETCH (RFC822 {1234}"   <-- FETCH line with literal size
        //   [1] "<1234 bytes of raw email>"   <-- the literal data (appended by readUntilTag)
        //   [2] ")"                            <-- closing paren
        //   [3] "aM OK FETCH completed"        <-- tagged response
        //
        // We just need to find the FETCH line and grab the next element.

        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/RFC822\s+\{\d+\}/i', $lines[$i]) && isset($lines[$i + 1])) {
                $rawEmail = $lines[$i + 1];
                file_put_contents($debugLog, "[{$timestamp}] Found RFC822 at index {$i}, length: " . strlen($rawEmail) . "\n", FILE_APPEND);
                // Validate it looks like an email (should start with headers)
                if (strlen($rawEmail) > 20) {
                    file_put_contents($debugLog, "[{$timestamp}] First 300 chars: " . substr($rawEmail, 0, 300) . "\n\n", FILE_APPEND);
                    return $rawEmail;
                }
            }

            // Also try BODY[] for some servers
            if (preg_match('/BODY\[\]\s+\{\d+\}/i', $lines[$i]) && isset($lines[$i + 1])) {
                $rawEmail = $lines[$i + 1];
                file_put_contents($debugLog, "[{$timestamp}] Found BODY[] at index {$i}, length: " . strlen($rawEmail) . "\n", FILE_APPEND);
                if (strlen($rawEmail) > 20) {
                    file_put_contents($debugLog, "[{$timestamp}] First 300 chars: " . substr($rawEmail, 0, 300) . "\n\n", FILE_APPEND);
                    return $rawEmail;
                }
            }
        }

        file_put_contents($debugLog, "[{$timestamp}] WARNING: No valid raw email found for ID {$id}\n\n", FILE_APPEND);
        return null;
    }
}
