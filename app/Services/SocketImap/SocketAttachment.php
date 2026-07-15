<?php

namespace App\Services\SocketImap;

/**
 * Socket-based IMAP attachment wrapper.
 * Provides a compatible interface with Ddeboer\Imap\Message\Attachment.
 */
class SocketAttachment
{
    private string $body;
    private string $rawHeaders;
    private array $parsedHeaders;
    private string $encoding;
    private ?string $filename = null;
    private ?string $contentType = null;

    public function __construct(string $body, string $rawHeaders, array $parsedHeaders, string $encoding)
    {
        $this->body = $body;
        $this->rawHeaders = $rawHeaders;
        $this->parsedHeaders = $parsedHeaders;
        $this->encoding = $encoding;

        // Extract filename from Content-Disposition
        $disposition = $parsedHeaders['content-disposition'] ?? '';
        if (preg_match('/filename[*]=UTF-8\'\'(.+)/i', $disposition, $m)) {
            $this->filename = rawurldecode($m[1]);
        } elseif (preg_match('/filename="?([^";\s]+)"?/i', $disposition, $m)) {
            $this->filename = trim($m[1], '"\'');
        }

        // Fallback: try Content-Type name parameter
        if (!$this->filename) {
            $contentType = $parsedHeaders['content-type'] ?? '';
            if (preg_match('/name[*]=UTF-8\'\'(.+)/i', $contentType, $m)) {
                $this->filename = rawurldecode($m[1]);
            } elseif (preg_match('/name="?([^";\s]+)"?/i', $contentType, $m)) {
                $this->filename = trim($m[1], '"\'');
            }
        }

        if (!$this->filename) {
            $this->filename = 'undefined';
        }

        $this->contentType = $parsedHeaders['content-type'] ?? 'application/octet-stream';
    }

    /**
     * Get the filename of the attachment.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get the MIME content type.
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Get the decoded content of the attachment.
     */
    public function getDecodedContent(): string
    {
        $encoding = strtolower(trim($this->encoding));

        if ($encoding === 'base64') {
            return base64_decode($this->body) ?: $this->body;
        }

        if ($encoding === 'quoted-printable') {
            return quoted_printable_decode($this->body);
        }

        return $this->body;
    }

    /**
     * Get a structure-like object compatible with ddeboer/imap.
     * Used by TMail's formatMessage to check content-id for inline images.
     */
    public function getStructure(): object
    {
        return (object) [
            'id' => $this->parsedHeaders['content-id'] ?? '',
            'type' => $this->contentType,
            'encoding' => $this->encoding,
        ];
    }

    /**
     * Get the raw headers (for debugging).
     */
    public function getRawHeaders(): string
    {
        return $this->rawHeaders;
    }
}
