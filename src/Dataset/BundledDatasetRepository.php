<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Dataset;

use JOOservices\UserAgent\Contract\DatasetRepository;
use JOOservices\UserAgent\Exceptions\InvalidDatasetException;

final class BundledDatasetRepository implements DatasetRepository
{
    #[\Override]
    public function load(): Dataset
    {
        $directory = realpath(__DIR__ . '/../../resources/dataset/bundled');
        if ($directory === false) {
            throw new InvalidDatasetException('Bundled dataset directory is unavailable.');
        }
        $expected = realpath(__DIR__ . '/../../resources/dataset');
        if ($expected === false || !str_starts_with($directory . DIRECTORY_SEPARATOR, $expected . DIRECTORY_SEPARATOR)) {
            throw new InvalidDatasetException('Bundled dataset resolved outside its resource jail.');
        }

        $files = [];
        $decoded = [];
        foreach (array_merge(['manifest.json'], Schema::PAYLOAD_FILES) as $name) {
            $path = realpath($directory . DIRECTORY_SEPARATOR . $name);
            if ($path === false || !str_starts_with($path, $directory . DIRECTORY_SEPARATOR)) {
                throw new InvalidDatasetException("Required dataset file {$name} is unavailable.");
            }
            $bytes = file_get_contents($path);
            if ($bytes === false) {
                throw new InvalidDatasetException("Unable to read dataset file {$name}.");
            }
            try {
                $value = json_decode($bytes, true, 32, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new InvalidDatasetException("Invalid JSON in {$name}.", previous: $exception);
            }
            if (!is_array($value)) {
                throw new InvalidDatasetException("Dataset file {$name} must contain an array or object.");
            }
            /** @var array<string, mixed>|list<mixed> $value */
            if ($name === 'manifest.json') {
                if (array_is_list($value)) {
                    throw new InvalidDatasetException('Dataset manifest must be an object.');
                }
                /** @var array<string, mixed> $value */
                $manifest = $value;
                continue;
            }
            $files[$name] = $bytes;
            $decoded[$name] = $value;
        }
        if (!isset($manifest)) {
            throw new InvalidDatasetException('Dataset manifest is missing.');
        }

        return (new DatasetValidator())->validate(
            $manifest,
            $decoded,
            (new DatasetChecksum())->calculate($files),
        );
    }
}
