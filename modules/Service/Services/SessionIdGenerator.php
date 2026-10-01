<?php

namespace Modules\Service\Services;

use Illuminate\Support\Str;

/**
 * Generates and validates session IDs for infinite scroll
 */
class SessionIdGenerator
{
    /**
     * Generate a new session ID
     * 
     * @param int $userId User ID
     * @return string Session ID
     */
    public static function generate(int $userId): string
    {
        $timestamp = microtime(true);
        $random = Str::random(16);
        
        return hash('sha256', "{$userId}:{$timestamp}:{$random}");
    }

    /**
     * Validate session ID format
     * 
     * @param string|null $sessionId
     * @return bool
     */
    public static function isValid(?string $sessionId): bool
    {
        if (empty($sessionId)) {
            return false;
        }
        
        // Should be 64 character hex string (sha256)
        return preg_match('/^[a-f0-9]{64}$/', $sessionId) === 1;
    }

    /**
     * Get or generate session ID
     * 
     * @param string|null $sessionId Provided session ID
     * @param int $userId User ID for generation if needed
     * @return string Valid session ID
     */
    public static function getOrGenerate(?string $sessionId, int $userId): string
    {
        if (self::isValid($sessionId)) {
            return $sessionId;
        }
        
        return self::generate($userId);
    }
}
