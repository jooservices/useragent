<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\ValueObjects;

use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;
use JOOservices\UserAgent\Domain\ValueObjects\DatasetMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \JOOservices\UserAgent\Domain\ValueObjects\DatasetMetadata
 */
final class DatasetMetadataTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function test_creates_with_all_properties(): void
    {
        $name = $this->faker->word();
        $version = $this->faker->numerify('#.#.#');
        $updatedAt = new DateTimeImmutable;
        $license = $this->faker->word();
        $source = $this->faker->url();

        $metadata = new DatasetMetadata($name, $version, $updatedAt, $license, $source);

        $this->assertSame($name, $metadata->name);
        $this->assertSame($version, $metadata->version);
        $this->assertSame($updatedAt, $metadata->updatedAt);
        $this->assertSame($license, $metadata->license);
        $this->assertSame($source, $metadata->source);
    }

    public function test_creates_from_array_with_all_fields(): void
    {
        $data = [
            'name' => $this->faker->word(),
            'version' => '1.2.3',
            'updated_at' => '2026-01-20T10:00:00+00:00',
            'license' => 'MIT',
            'source' => $this->faker->url(),
        ];

        $metadata = DatasetMetadata::fromArray($data);

        $this->assertSame($data['name'], $metadata->name);
        $this->assertSame($data['version'], $metadata->version);
        $this->assertSame($data['license'], $metadata->license);
        $this->assertSame($data['source'], $metadata->source);
    }

    public function test_creates_from_array_with_defaults(): void
    {
        $metadata = DatasetMetadata::fromArray([]);

        $this->assertSame('unknown', $metadata->name);
        $this->assertSame('1.0.0', $metadata->version);
        $this->assertInstanceOf(DateTimeImmutable::class, $metadata->updatedAt);
        $this->assertSame('unknown', $metadata->license);
        $this->assertNull($metadata->source);
    }

    public function test_is_readonly(): void
    {
        $metadata = new DatasetMetadata(
            $this->faker->word(),
            '1.0.0',
            new DateTimeImmutable,
            'MIT'
        );

        $reflection = new ReflectionClass($metadata);

        $this->assertTrue($reflection->isReadOnly());
    }
}
