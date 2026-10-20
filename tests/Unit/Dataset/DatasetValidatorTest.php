<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Dataset;

use JOOservices\UserAgent\Dataset\DatasetChecksum;
use JOOservices\UserAgent\Dataset\DatasetValidator;
use JOOservices\UserAgent\Dataset\Schema;
use JOOservices\UserAgent\Exceptions\InvalidDatasetException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DatasetValidatorTest extends TestCase
{
    #[Test]
    public function test_DS_U01_checksum_tamper_fails_closed(): void
    {
        [$manifest, $decoded] = $this->payload();
        $this->expectException(InvalidDatasetException::class);
        (new DatasetValidator())->validate($manifest, $decoded, str_repeat('0', 64));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidMutation(): iterable
    {
        yield 'missing template' => ['missing-template'];
        yield 'unknown placeholder' => ['unknown-placeholder'];
        yield 'zero tuple weight' => ['zero-weight'];
        yield 'empty versions' => ['empty-versions'];
        yield 'duplicate tuple' => ['duplicate-tuple'];
        yield 'extra tuple key' => ['extra-key'];
        yield 'unsupported schema' => ['schema'];
        yield 'unsafe model token' => ['unsafe-model'];
        yield 'invalid fetched date' => ['invalid-fetched-date'];
        yield 'invalid effective date' => ['invalid-effective-date'];
        yield 'impossible effective date' => ['impossible-effective-date'];
        yield 'invalid release date' => ['invalid-release-date'];
        yield 'release after effective date' => ['future-release'];
        yield 'unknown engine override' => ['unknown-engine-override'];
        yield 'unknown override platform' => ['unknown-override-platform'];
        yield 'override without compatible tuple' => ['unused-engine-override'];
    }

    #[Test]
    #[DataProvider('invalidMutation')]
    public function test_DS_U_W_SEC_invalid_dataset_shapes_are_rejected(string $mutation): void
    {
        [$manifest, $decoded, $checksum] = $this->payload();
        /** @var list<array<string, mixed>> $compatibility */
        $compatibility = $decoded['compatibility.json'];
        /** @var array<string, string> $templates */
        $templates = $decoded['templates.json'];
        /** @var array<string, array{engine: string, versions: list<array{major: int, full: string, releasedAt: string, weight: int|float}>, engineOverrides?: array<string, string>}> $browsers */
        $browsers = $decoded['browsers.json'];
        /** @var array<string, list<array<string, mixed>>> $models */
        $models = $decoded['models.json'];

        match ($mutation) {
            'missing-template' => $templates = array_slice($templates, 1, null, true),
            'unknown-placeholder' => $templates['chrome|desktop|windows'] .= ' {nope}',
            'zero-weight' => $compatibility[0]['weight'] = 0,
            'empty-versions' => $browsers['chrome']['versions'] = [],
            'duplicate-tuple' => $compatibility[] = $compatibility[0],
            'extra-key' => $compatibility[0]['unexpected'] = true,
            'schema' => $manifest['schemaVersion'] = 3,
            'unsafe-model' => $models['android'][0]['token'] = "bad\nvalue",
            'invalid-fetched-date' => $manifest['fetchedAt'] = 'yesterday',
            'invalid-effective-date' => $manifest['effectiveAt'] = 'not-a-date',
            'impossible-effective-date' => $manifest['effectiveAt'] = '2026-02-30',
            'invalid-release-date' => $browsers['chrome']['versions'][0]['releasedAt'] = '2026-13-01',
            'future-release' => $browsers['chrome']['versions'][0]['releasedAt'] = '2026-08-29',
            'unknown-engine-override' => $browsers['chrome']['engineOverrides']['ios'] = 'invalid',
            'unknown-override-platform' => $browsers['chrome']['engineOverrides']['symbian'] = 'webkit',
            'unused-engine-override' => $browsers['safari']['engineOverrides']['android'] = 'webkit',
            default => self::fail('Unknown mutation.'),
        };
        $decoded['compatibility.json'] = $compatibility;
        $decoded['templates.json'] = $templates;
        $decoded['browsers.json'] = $browsers;
        $decoded['models.json'] = $models;

        $this->expectException(InvalidDatasetException::class);
        (new DatasetValidator())->validate($manifest, $decoded, $checksum);
    }

    /** @return array{array<string, mixed>, array<string, mixed>, string} */
    private function payload(): array
    {
        $directory = dirname(__DIR__, 3) . '/resources/dataset/bundled';
        $raw = [];
        $decoded = [];
        foreach (Schema::PAYLOAD_FILES as $name) {
            $bytes = file_get_contents($directory . '/' . $name);
            self::assertIsString($bytes);
            $value = json_decode($bytes, true, 32, JSON_THROW_ON_ERROR);
            self::assertIsArray($value);
            $raw[$name] = $bytes;
            $decoded[$name] = $value;
        }
        $manifestBytes = file_get_contents($directory . '/manifest.json');
        self::assertIsString($manifestBytes);
        $manifest = json_decode($manifestBytes, true, 32, JSON_THROW_ON_ERROR);
        self::assertIsArray($manifest);

        /** @var array<string, mixed> $manifest */
        /** @var array<string, string> $raw */
        return [$manifest, $decoded, (new DatasetChecksum())->calculate($raw)];
    }
}
