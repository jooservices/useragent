<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Facade;

use JOOservices\UserAgent\Domain\Architecture;
use JOOservices\UserAgent\Domain\BatchResult;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\GenerationResult;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Domain\SelectionPolicyId;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Generator;

final readonly class RequestBuilder
{
    public function __construct(
        private GenerationRequest $request = new GenerationRequest(),
        private ?Generator $generator = null,
    ) {
    }

    public function chrome(): self
    {
        return $this->change('browser', BrowserFamily::Chrome);
    }
    public function firefox(): self
    {
        return $this->change('browser', BrowserFamily::Firefox);
    }
    public function safari(): self
    {
        return $this->change('browser', BrowserFamily::Safari);
    }
    public function edge(): self
    {
        return $this->change('browser', BrowserFamily::Edge);
    }
    public function desktop(): self
    {
        return $this->change('device', DeviceClass::Desktop);
    }
    public function mobile(): self
    {
        return $this->change('device', DeviceClass::Mobile);
    }
    public function tablet(): self
    {
        return $this->change('device', DeviceClass::Tablet);
    }
    public function windows(): self
    {
        return $this->change('platform', Platform::Windows);
    }
    public function macos(): self
    {
        return $this->change('platform', Platform::MacOS);
    }
    public function linux(): self
    {
        return $this->change('platform', Platform::Linux);
    }
    public function android(): self
    {
        return $this->change('platform', Platform::Android);
    }
    public function ios(): self
    {
        return $this->change('platform', Platform::iOS);
    }
    public function chromeos(): self
    {
        return $this->change('platform', Platform::ChromeOS);
    }
    public function locale(string $locale): self
    {
        return $this->change('locale', $locale);
    }
    public function architecture(Architecture $architecture): self
    {
        return $this->change('architecture', $architecture);
    }
    public function version(int $majorExact): self
    {
        return $this->changeMany(['versionExact' => $majorExact, 'versionMin' => null, 'versionMax' => null]);
    }
    public function versionMin(int $major): self
    {
        return $this->changeMany(['versionExact' => null, 'versionMin' => $major]);
    }
    public function versionMax(int $major): self
    {
        return $this->changeMany(['versionExact' => null, 'versionMax' => $major]);
    }
    public function recent(int $months = 6): self
    {
        return $this->change('releasedWithinMonths', $months);
    }
    public function selection(SelectionPolicyId $policy): self
    {
        return $this->change('selection', $policy);
    }
    public function seed(int $seed): self
    {
        return $this->change('seed', $seed);
    }
    public function datasetRevision(string $revision): self
    {
        return $this->change('datasetRevision', $revision);
    }

    public function toRequest(): GenerationRequest
    {
        return $this->request;
    }

    public function generate(): string
    {
        return $this->generateResult()->userAgent;
    }

    public function generateResult(): GenerationResult
    {
        return ($this->generator ?? Generator::create())->generate($this->request);
    }

    public function generateMany(int $count, UniquePolicy $unique = UniquePolicy::Fail): BatchResult
    {
        return ($this->generator ?? Generator::create())->generateMany($this->request, $count, $unique);
    }

    /** @return list<string> */
    public function generatePool(int $count, UniquePolicy $unique = UniquePolicy::Fail): array
    {
        return $this->generateMany($count, $unique)->userAgents();
    }

    private function change(string $property, object | string | int | null $value): self
    {
        return $this->changeMany([$property => $value]);
    }

    /** @param array<string, object|string|int|null> $changes */
    private function changeMany(array $changes): self
    {
        return clone($this, ['request' => $this->request->with($changes)]);
    }
}
