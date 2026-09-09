<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Support;

use JOOservices\UserAgent\Core\BatchGenerator;
use JOOservices\UserAgent\Core\CompatibilityResolver;
use JOOservices\UserAgent\Core\GenerationSession;
use JOOservices\UserAgent\Core\ProfileSelector;
use JOOservices\UserAgent\Core\Renderer;
use JOOservices\UserAgent\Dataset\Dataset;
use JOOservices\UserAgent\Dataset\DatasetChecksum;
use JOOservices\UserAgent\Dataset\DatasetValidator;
use JOOservices\UserAgent\Dataset\Schema;
use JOOservices\UserAgent\Generator;
use JOOservices\UserAgent\GeneratorConfig;
use JOOservices\UserAgent\History\InMemoryHistoryStore;
use JOOservices\UserAgent\Random\MtRandomSource;
use RuntimeException;

final class FixtureDataset
{
    public static function dataset(): Dataset
    {
        $directory = dirname(__DIR__) . '/Fixtures/dataset';
        $raw = [];
        $decoded = [];
        foreach (Schema::PAYLOAD_FILES as $name) {
            $bytes = file_get_contents($directory . '/' . $name);
            if ($bytes === false) {
                throw new RuntimeException("Missing fixture file {$name}.");
            }
            $value = json_decode($bytes, true, 32, JSON_THROW_ON_ERROR);
            if (!is_array($value)) {
                throw new RuntimeException("Invalid fixture file {$name}.");
            }
            $raw[$name] = $bytes;
            $decoded[$name] = $value;
        }
        $manifestBytes = file_get_contents($directory . '/manifest.json');
        if ($manifestBytes === false) {
            throw new RuntimeException('Missing fixture manifest.');
        }
        $manifest = json_decode($manifestBytes, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($manifest) || array_is_list($manifest)) {
            throw new RuntimeException('Invalid fixture manifest.');
        }

        /** @var array<string, mixed> $manifest */
        /** @var array<string, string> $raw */
        return (new DatasetValidator())->validate(
            $manifest,
            $decoded,
            (new DatasetChecksum())->calculate($raw),
        );
    }

    public static function generator(?GeneratorConfig $config = null): Generator
    {
        $config ??= new GeneratorConfig();
        $factory = static fn(?int $seed): GenerationSession => new GenerationSession(
            $seed === null ? new MtRandomSource() : MtRandomSource::seeded($seed),
            new InMemoryHistoryStore($config->historySize),
            $seed,
        );

        return new Generator(
            self::dataset(),
            new CompatibilityResolver(),
            new ProfileSelector(),
            new Renderer(),
            new BatchGenerator(),
            $factory,
            $config,
        );
    }
}
