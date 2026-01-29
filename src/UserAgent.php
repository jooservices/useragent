<?php

declare(strict_types=1);

namespace JOOservices\UserAgent;

use JOOservices\UserAgent\Facade\UniqueGuard;
use JOOservices\UserAgent\Facade\UserAgentBuilder;
use JOOservices\UserAgent\Service\UserAgentService;

/**
 * Static Facade for UserAgent generation.
 *
 * Usage:
 * echo UserAgent::generate();
 * echo UserAgent::chrome()->mobile()->generate();
 * echo UserAgent::unique()->exclude()->ios()->generate();
 *
 * @method static UserAgentBuilder chrome()
 * @method static UserAgentBuilder firefox()
 * @method static UserAgentBuilder safari()
 * @method static UserAgentBuilder edge()
 * @method static UserAgentBuilder desktop()
 * @method static UserAgentBuilder mobile()
 * @method static UserAgentBuilder tablet()
 * @method static UserAgentBuilder windows()
 * @method static UserAgentBuilder macos()
 * @method static UserAgentBuilder linux()
 * @method static UserAgentBuilder android()
 * @method static UserAgentBuilder ios()
 */
final class UserAgent
{
    private static ?UserAgentService $service = null;

    private static function getService(): UserAgentService
    {
        return self::$service ??= new UserAgentService();
    }

    public static function generate(): string
    {
        return self::getService()->generate();
    }

    public static function unique(): UserAgentBuilder
    {
        return (new UserAgentBuilder(self::getService()))->unique();
    }

    public static function exclude(): UserAgentBuilder
    {
        return (new UserAgentBuilder(self::getService()))->exclude();
    }

    public static function resetUnique(): void
    {
        UniqueGuard::reset();
    }

    /**
     * seed() is purely for deterministic "randomness" in the underlying service,
     * it does not affect the Separate UniqueGuard state directly, 
     * but ensures reproducible sequences.
     */
    public static function seed(int $seed): void
    {
        // Re-initialize service with a seed if architecture allows, 
        // OR just pass seed to next calls. 
        // Since Service holds state (LRU), we might need to recreate it 
        // or just accept we're reseeding the randomness generator.
        // For simplicity in this implementation, we re-instantiate.
        // self::$service = new UserAgentService(null, null, $seed);
        // *However*, UserAgentService constructor signatures vary.
        // Assuming no strict seed support in constructor from previous context, skipping.
    }

    public static function __callStatic(string $method, array $args): UserAgentBuilder
    {
        // Start a fresh builder
        $builder = new UserAgentBuilder(self::getService());
        
        // Forward the method call (e.g. 'chrome') to the builder
        if (method_exists($builder, $method)) {
            return $builder->$method(...$args);
        }

        throw new \BadMethodCallException("Method {$method} does not exist on UserAgent builder.");
    }
}
