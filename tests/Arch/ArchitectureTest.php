<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Arch;

use JOOservices\Dto\Core\Dto;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\GenerationResult;
use JOOservices\UserAgent\Generator;
use JOOservices\UserAgent\UserAgent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use SplFileInfo;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function test_ARCH01_ARCH04_public_engine_contract(): void
    {
        $class = new ReflectionClass(Generator::class);
        self::assertTrue($class->isFinal());
        $returnType = (new ReflectionMethod(Generator::class, 'generate'))->getReturnType();
        self::assertInstanceOf(ReflectionNamedType::class, $returnType);
        self::assertSame(GenerationResult::class, $returnType->getName());
        $requestClass = GenerationRequest::class;
        $dtoClass = Dto::class;
        self::assertTrue((new ReflectionClass($requestClass))->isSubclassOf($dtoClass));
        self::assertSame([], (new ReflectionClass(UserAgent::class))->getStaticProperties());
    }

    #[Test]
    public function test_ARCH07_ARCH09_forbidden_runtime_symbols_are_absent(): void
    {
        $source = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/src'));
        foreach ($iterator as $file) {
            self::assertInstanceOf(SplFileInfo::class, $file);
            if ($file->isFile() && $file->getExtension() === 'php') {
                $source .= file_get_contents($file->getPathname());
            }
        }
        foreach (['mt_srand(', 'mt_rand(', 'array_rand(', 'eval(', 'shell_exec(', 'class UserAgentService', 'class GenerationSpec', 'class UniqueGuard'] as $needle) {
            self::assertStringNotContainsString($needle, $source);
        }
    }
}
