<?php

namespace App\Services\SocketImap;

/**
 * Socket-based IMAP search expression builder.
 * Provides a compatible interface with Ddeboer\Imap\SearchExpression.
 * Builds raw IMAP SEARCH criteria strings.
 */
class SocketSearchExpression
{
    private array $conditions = [];

    /**
     * Add a search condition.
     * Accepts SocketSearchCondition objects or raw strings.
     *
     * @param SocketSearchCondition|string $condition
     * @return $this
     */
    public function addCondition($condition): self
    {
        if ($condition instanceof SocketSearchCondition) {
            $this->conditions[] = $condition->toImapString();
        } elseif (is_string($condition)) {
            $this->conditions[] = $condition;
        }
        return $this;
    }

    /**
     * Convert the search expression to an IMAP SEARCH criteria string.
     */
    public function toImapString(): string
    {
        if (empty($this->conditions)) {
            return 'ALL';
        }
        return implode(' ', $this->conditions);
    }
}

/**
 * Base class for search conditions.
 */
abstract class SocketSearchCondition
{
    abstract public function toImapString(): string;
}

/**
 * Search for messages TO a specific email address.
 */
class SocketSearchTo extends SocketSearchCondition
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function toImapString(): string
    {
        return 'TO "' . addslashes($this->email) . '"';
    }
}

/**
 * Search for messages CC a specific email address.
 */
class SocketSearchCc extends SocketSearchCondition
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function toImapString(): string
    {
        return 'CC "' . addslashes($this->email) . '"';
    }
}

/**
 * Search for messages SINCE a specific date.
 * Date format should be compatible with IMAP (d-Mon-yyyy).
 */
class SocketSearchSince extends SocketSearchCondition
{
    private \DateTimeInterface $date;

    public function __construct(\DateTimeInterface $date)
    {
        $this->date = $date;
    }

    public function toImapString(): string
    {
        return 'SINCE ' . $this->date->format('d-M-Y');
    }
}

/**
 * Search for messages BEFORE a specific date.
 */
class SocketSearchBefore extends SocketSearchCondition
{
    private \DateTimeInterface $date;

    public function __construct(\DateTimeInterface $date)
    {
        $this->date = $date;
    }

    public function toImapString(): string
    {
        return 'BEFORE ' . $this->date->format('d-M-Y');
    }
}
