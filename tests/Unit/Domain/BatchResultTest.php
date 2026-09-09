<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain;

use JOOservices\UserAgent\Domain\BatchResult;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Tests\Support\FixtureDataset;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BatchResultTest extends TestCase
{
    #[Test]
    public function testUserAgentsReturnsTheGeneratedStringsInOrder(): void
    {
        $first = FixtureDataset::generator()->generate(new GenerationRequest(seed: 1));
        $second = FixtureDataset::generator()->generate(new GenerationRequest(seed: 2));

        $batch = new BatchResult([$first, $second], 2, 2, 2, UniquePolicy::Fail);

        self::assertSame([$first->userAgent, $second->userAgent], $batch->userAgents());
    }
}
