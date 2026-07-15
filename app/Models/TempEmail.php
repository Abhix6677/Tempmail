<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TempEmail extends Model
{
    protected $fillable = [
        'generated_address',
        'session_id',
        'assigned_to',
        'expires_at',
        'assigned_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: only currently assigned (active + non-expired + bound to a session).
     */
    public function scopeAssigned($query)
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('session_id')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: unassigned variants (available for claiming).
     */
    public function scopeAvailable($query)
    {
        return $query
            ->where('is_active', true)
            ->whereNull('session_id');
    }

    /**
     * Scope: variants that have expired and should be released.
     */
    public function scopeExpired($query)
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('session_id')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Atomically claim an available variant for a session.
     * Uses a database row-level lock to prevent race conditions.
     */
    public static function claimForSession(string $sessionId, string $assignedTo = null): ?self
    {
        return DB::transaction(function () use ($sessionId, $assignedTo) {
            // Pick a random available variant (lock the row to prevent concurrent claims).
            // Using inRandomOrder() ensures different users get different dot-variants
            // instead of always the same lowest-id one.
            $variant = self::available()
                ->lockForUpdate()
                ->inRandomOrder()
                ->first();

            if (!$variant) {
                return null;
            }

            $variant->update([
                'session_id' => $sessionId,
                'assigned_to' => $assignedTo,
                'assigned_at' => now(),
                'expires_at' => now()->addHours(2), // default 2-hour expiry
            ]);

            return $variant->fresh();
        });
    }

    /**
     * Release all variants assigned to a session back into the pool.
     */
    public static function releaseSession(string $sessionId): int
    {
        return self::where('session_id', $sessionId)
            ->update([
                'session_id' => null,
                'assigned_to' => null,
                'assigned_at' => null,
                'expires_at' => null,
            ]);
    }

    /**
     * Release all expired variants back into the pool.
     */
    public static function releaseExpired(): int
    {
        return self::expired()
            ->update([
                'session_id' => null,
                'assigned_to' => null,
                'assigned_at' => null,
                'expires_at' => null,
            ]);
    }

    /**
     * Ensure all possible dot-variant records exist in the database
     * for a given base Gmail address.
     */
    public static function seedVariants(string $baseEmail): int
    {
        [$username, $domain] = explode('@', strtolower(trim($baseEmail)));
        $username = str_replace('.', '', $username); // Gmail ignores dots
        $length = strlen($username);
        $max = pow(2, $length - 1);
        $seeded = 0;

        // Start from index 1 to skip the bare address (no dots).
        // Index 0 = "abhixz591@gmail.com" (no dot insertion). We skip it so
        // users always get a dot-variant like "a.bhixz591@gmail.com".
        for ($index = 1; $index < $max; $index++) {
            $binary = str_pad(decbin($index), $length - 1, '0', STR_PAD_LEFT);
            $result = '';
            for ($i = 0; $i < $length; $i++) {
                $result .= $username[$i];
                if ($i < $length - 1 && $binary[$i] === '1') {
                    $result .= '.';
                }
            }
            $address = $result . '@' . $domain;

            self::firstOrCreate(
                ['generated_address' => $address],
                [
                    'is_active' => true,
                    'expires_at' => null,
                ]
            );
            $seeded++;
        }

        // Also ensure the bare address (index 0) exists but mark it reserved
        // so claimForSession never assigns it to a user.
        $bareAddress = $username . '@' . $domain;
        self::firstOrCreate(
            ['generated_address' => $bareAddress],
            [
                'is_active' => false,
                'expires_at' => null,
            ]
        );
        // If it already existed as active, deactivate it
        self::where('generated_address', $bareAddress)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return $seeded;
    }
}
