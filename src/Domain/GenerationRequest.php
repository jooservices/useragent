<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

use JOOservices\Dto\Core\Dto;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;

final class GenerationRequest extends Dto
{
    public function __construct(
        public readonly ?BrowserFamily $browser = null,
        public readonly ?DeviceClass $device = null,
        public readonly ?Platform $platform = null,
        public readonly ?string $locale = null,
        public readonly ?Architecture $architecture = null,
        public readonly ?int $versionExact = null,
        public readonly ?int $versionMin = null,
        public readonly ?int $versionMax = null,
        public readonly ?int $releasedWithinMonths = null,
        public readonly SelectionPolicyId $selection = SelectionPolicyId::Weighted,
        public readonly ?int $seed = null,
        public readonly ?string $datasetRevision = null,
    ) {
        $this->guard();
    }

    private function guard(): void
    {
        if ($this->versionExact !== null && ($this->versionMin !== null || $this->versionMax !== null)) {
            throw new InvalidRequestException('versionExact cannot be combined with versionMin or versionMax.');
        }
        foreach ([$this->versionExact, $this->versionMin, $this->versionMax] as $version) {
            if ($version !== null && ($version < 1 || $version > 999)) {
                throw new InvalidRequestException('Versions must be between 1 and 999.');
            }
        }
        if ($this->versionMin !== null && $this->versionMax !== null && $this->versionMin > $this->versionMax) {
            throw new InvalidRequestException('versionMin cannot exceed versionMax.');
        }
        if ($this->releasedWithinMonths !== null && $this->releasedWithinMonths < 1) {
            throw new InvalidRequestException('releasedWithinMonths must be at least one.');
        }
        if ($this->seed !== null && $this->seed < 0) {
            throw new InvalidRequestException('seed cannot be negative.');
        }
        if ($this->locale !== null && (strlen($this->locale) > 32 || preg_match('/^[a-z]{2,8}(?:-[A-Za-z0-9]{1,8})?$/D', $this->locale) !== 1)) {
            throw new InvalidRequestException('locale must be a simple BCP 47 language tag.');
        }
        if ($this->datasetRevision !== null && (strlen($this->datasetRevision) > 64 || str_contains($this->datasetRevision, '..'))) {
            throw new InvalidRequestException('datasetRevision is invalid.');
        }
    }
}
