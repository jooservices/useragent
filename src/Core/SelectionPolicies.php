<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use JOOservices\UserAgent\Contract\SelectionPolicy;
use JOOservices\UserAgent\Domain\SelectionPolicyId;

final readonly class SelectionPolicies
{
    public function __construct(
        private WeightedPolicy $weighted = new WeightedPolicy(),
        private UniformPolicy $uniform = new UniformPolicy(),
        private RoundRobinPolicy $roundRobin = new RoundRobinPolicy(),
    ) {
    }

    public function for(SelectionPolicyId $policyId): SelectionPolicy
    {
        return match ($policyId) {
            SelectionPolicyId::Weighted => $this->weighted,
            SelectionPolicyId::Uniform => $this->uniform,
            SelectionPolicyId::RoundRobin => $this->roundRobin,
        };
    }
}
