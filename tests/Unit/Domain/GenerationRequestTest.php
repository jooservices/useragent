<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain;

use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;
use JOOservices\UserAgent\Tests\Support\FixtureDataset;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GenerationRequestTest extends TestCase
{
    #[Test]
    public function testProfileIdentityIsStableAndHumanReadable(): void
    {
        $profile = FixtureDataset::generator()->generate(new GenerationRequest(seed: 42))->profile;

        self::assertSame(
            sprintf('%s/%s %s/%s %s', $profile->browser->value, $profile->browserVersion, $profile->platform->value, $profile->device->value, $profile->architecture->value),
            $profile->identity(),
        );
    }

    #[Test]
    public function test_DTO_H01_H03_typed_and_immutable_copy(): void
    {
        $request = new GenerationRequest(browser: BrowserFamily::Chrome);
        $copy = $request->with(browser: BrowserFamily::Firefox);
        self::assertSame(BrowserFamily::Chrome, $request->browser);
        self::assertSame(BrowserFamily::Firefox, $copy->browser);
    }

    #[Test]
    public function test_DTO_H02_from_hydrates_backed_enum(): void
    {
        self::assertSame(BrowserFamily::Chrome, GenerationRequest::from(['browser' => 'chrome'])->browser);
    }

    /** @return iterable<string, array{array<string, int|string>}> */
    public static function invalidRequests(): iterable
    {
        yield 'zero exact' => [['versionExact' => 0]];
        yield 'reversed range' => [['versionMin' => 10, 'versionMax' => 5]];
        yield 'exact plus min' => [['versionExact' => 5, 'versionMin' => 4]];
        yield 'negative seed' => [['seed' => -1]];
        yield 'zero recent' => [['releasedWithinMonths' => 0]];
        yield 'invalid locale' => [['locale' => 'EN_US']];
        yield 'header locale' => [['locale' => "en\r\nX"]];
        yield 'path revision' => [['datasetRevision' => '../secret']];
    }

    /** @param array<string, int|string> $values */
    #[Test]
    #[DataProvider('invalidRequests')]
    public function test_DTO_U_SEC_invalid_values_fail_closed(array $values): void
    {
        $this->expectException(InvalidRequestException::class);
        GenerationRequest::from($values);
    }
}
