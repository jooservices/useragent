<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Console;

use Closure;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Exceptions\ConsoleException;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;
use JOOservices\UserAgent\Exceptions\UserAgentException;
use JOOservices\UserAgent\Generator;

final class Application
{
    private readonly Closure $stdout;
    private readonly Closure $stderr;

    public function __construct(
        private readonly Generator $generator,
        ?Closure $stdout = null,
        ?Closure $stderr = null,
        private readonly ArgvParser $parser = new ArgvParser(),
        private readonly InputMapper $mapper = new InputMapper(),
        private readonly OutputFormatter $formatter = new OutputFormatter(),
    ) {
        $this->stdout = $stdout ?? static fn(string $text): int | false => fwrite(STDOUT, $text);
        $this->stderr = $stderr ?? static fn(string $text): int | false => fwrite(STDERR, $text);
    }

    /** @param list<string> $argv */
    public function run(array $argv): int
    {
        try {
            $input = $this->parser->parse($argv);
            if (($input['help'] ?? false) === true) {
                ($this->stdout)($this->help());

                return 0;
            }
            $request = $this->mapper->request($input);
            $count = $this->mapper->count($input);
            $format = $this->mapper->format($input);
        } catch (ConsoleException | InvalidRequestException $exception) {
            ($this->stderr)($exception->getMessage() . "\n");

            return 2;
        }

        try {
            $batch = $this->generator->generateMany(
                $request,
                $count,
                ($input['unique'] ?? false) === true ? UniquePolicy::Fail : UniquePolicy::AllowDuplicates,
            );
            ($this->stdout)($this->formatter->format($batch, $format));

            return 0;
        } catch (UserAgentException $exception) {
            ($this->stderr)($exception->getMessage() . "\n");

            return 1;
        }
    }

    private function help(): string
    {
        return <<<'HELP'
useragent [options]

--browser=chrome|firefox|safari|edge
--device=desktop|mobile|tablet
--os=windows|macos|linux|android|ios|chromeos
--locale=en-US --arch=x86_64|arm64
--version=145 --version-min=120 --version-max=145 --recent=6
--selection=weighted|uniform|round_robin --seed=42
--count=1 --unique --format=text|json|ndjson --help
HELP . "\n";
    }
}
