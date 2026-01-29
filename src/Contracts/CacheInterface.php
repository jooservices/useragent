<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contracts;

/**
 * Cache interface for storing temporary data.
 */
interface CacheInterface
{
    /**
     * Retrieve value from cache.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store value in cache with optional TTL in seconds.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Check if key exists in cache.
     */
    public function has(string $key): bool;

    /**
     * Remove value from cache.
     */
    public function delete(string $key): bool;
}
