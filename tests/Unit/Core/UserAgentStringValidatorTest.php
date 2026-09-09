<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Core;

use JOOservices\UserAgent\Core\UserAgentStringValidator;
use JOOservices\UserAgent\Exceptions\RenderException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserAgentStringValidatorTest extends TestCase
{
    #[Test]
    public function testRejectsAllControlCharacters(): void
    {
        $this->expectException(RenderException::class);

        (new UserAgentStringValidator())->assertValid("Mozilla/5.0\tTest");
    }
}
