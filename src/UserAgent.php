<?php

declare(strict_types=1);

namespace JOOservices\UserAgent;

use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\GenerationResult;
use JOOservices\UserAgent\Facade\RequestBuilder;

final class UserAgent
{
    public static function generate(): string
    {
        return self::generateResult()->userAgent;
    }
    public static function generateResult(): GenerationResult
    {
        return Generator::create()->generate();
    }
    public static function builder(): RequestBuilder
    {
        return new RequestBuilder();
    }
    public static function chrome(): RequestBuilder
    {
        return self::builder()->chrome();
    }
    public static function firefox(): RequestBuilder
    {
        return self::builder()->firefox();
    }
    public static function safari(): RequestBuilder
    {
        return self::builder()->safari();
    }
    public static function edge(): RequestBuilder
    {
        return self::builder()->edge();
    }
    public static function desktop(): RequestBuilder
    {
        return self::builder()->desktop();
    }
    public static function mobile(): RequestBuilder
    {
        return self::builder()->mobile();
    }
    public static function tablet(): RequestBuilder
    {
        return self::builder()->tablet();
    }
    public static function windows(): RequestBuilder
    {
        return self::builder()->windows();
    }
    public static function macos(): RequestBuilder
    {
        return self::builder()->macos();
    }
    public static function linux(): RequestBuilder
    {
        return self::builder()->linux();
    }
    public static function android(): RequestBuilder
    {
        return self::builder()->android();
    }
    public static function ios(): RequestBuilder
    {
        return self::builder()->ios();
    }
    public static function chromeos(): RequestBuilder
    {
        return self::builder()->chromeos();
    }

    /** @return list<\JOOservices\UserAgent\Domain\CompatibilityTuple> */
    public static function matrix(?GenerationRequest $filter = null): array
    {
        return Generator::create()->matrix($filter);
    }
}
