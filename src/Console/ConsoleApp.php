<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Console;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;
use Throwable;

final class ConsoleApp
{
    /** @var array<string, string> */
    private array $args = [];

    /**
     * @param array<int, string> $argv
     */
    public function __construct(
        private readonly array $argv,
        private readonly UserAgentService $service = new UserAgentService()
    ) {
        // Parse arguments: --key=value
        foreach ($this->argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = $parts[1] ?? 'true';
                $this->args[$key] = $value;
            }
        }
    }

    public function run(): int
    {
        try {
            $count = (int) ($this->args['count'] ?? 1);
            $count = max(1, min($count, 100)); // Safety limits

            $spec = $this->buildSpec();

            for ($i = 0; $i < $count; $i++) {
                echo $this->service->generate($spec) . PHP_EOL;
            }

            return 0;
        } catch (Throwable $e) {
            fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);

            return 1;
        }
    }

    private function buildSpec(): ?GenerationSpec
    {
        // If no constraints, return null for random generation
        if (empty($this->args)) {
            return null;
        }

        $builder = GenerationSpec::create();

        if (isset($this->args['browser'])) {
            $browser = BrowserFamily::tryFrom(strtolower($this->args['browser']));
            if ($browser) {
                $builder->browser($browser);
            }
        }

        if (isset($this->args['device'])) {
            $device = DeviceType::tryFrom(strtolower($this->args['device']));
            if ($device) {
                $builder->device($device);
            }
        }

        if (isset($this->args['os'])) {
            $os = OperatingSystem::tryFrom($this->mapOsName($this->args['os']));
            if ($os) {
                $builder->os($os);
            }
        }

        return $builder->build();
    }

    private function mapOsName(string $input): string
    {
        $map = [
            'mac' => 'macos',
            'win' => 'windows',
            'linux' => 'linux',
            'android' => 'android',
            'ios' => 'ios',
        ];

        return $map[strtolower($input)] ?? strtolower($input);
    }
}
