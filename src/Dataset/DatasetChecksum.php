<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Dataset;

final class DatasetChecksum
{
    /** @param array<string, string> $files */
    public function calculate(array $files): string
    {
        $payload = '';
        foreach (Schema::PAYLOAD_FILES as $name) {
            $payload .= $files[$name] ?? '';
        }

        return hash('sha256', $payload);
    }
}
