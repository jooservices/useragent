<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Exceptions;

use JOOservices\UserAgent\Exceptions\ProviderException;
use JOOservices\UserAgent\Exceptions\UserAgentException;
use PHPUnit\Framework\TestCase;

final class ProviderExceptionTest extends TestCase
{
    public function test_it_extends_user_agent_exception(): void
    {
        $exception = ProviderException::fileNotFound('/path/to/file.json');

        $this->assertInstanceOf(UserAgentException::class, $exception);
        $this->assertInstanceOf(ProviderException::class, $exception);
    }

    public function test_file_not_found_creates_exception_with_correct_message(): void
    {
        $exception = ProviderException::fileNotFound('/path/to/data.json');

        $this->assertSame('File not found: /path/to/data.json', $exception->getMessage());
    }

    public function test_load_failed_creates_exception_with_correct_message(): void
    {
        $exception = ProviderException::loadFailed('https://example.com/data', 'Connection timeout');

        $this->assertSame('Failed to load from https://example.com/data: Connection timeout', $exception->getMessage());
    }

    public function test_unsupported_creates_exception_with_correct_message(): void
    {
        $exception = ProviderException::unsupported('CustomProvider');

        $this->assertSame('Provider CustomProvider does not support current configuration', $exception->getMessage());
    }

    public function test_file_not_found_with_empty_path(): void
    {
        $exception = ProviderException::fileNotFound('');

        $this->assertSame('File not found: ', $exception->getMessage());
    }

    public function test_load_failed_with_various_reasons(): void
    {
        $reasons = [
            'Invalid JSON',
            'Network error',
            'Permission denied',
            'File corrupted',
        ];

        foreach ($reasons as $reason) {
            $exception = ProviderException::loadFailed('source', $reason);
            $this->assertStringContainsString($reason, $exception->getMessage());
        }
    }

    public function test_unsupported_with_namespaced_class(): void
    {
        $exception = ProviderException::unsupported('JOOservices\\UserAgent\\Providers\\CustomProvider');

        $this->assertStringContainsString('JOOservices\\UserAgent\\Providers\\CustomProvider', $exception->getMessage());
    }
}
