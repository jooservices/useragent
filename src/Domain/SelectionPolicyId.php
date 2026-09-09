<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

enum SelectionPolicyId: string
{
    case Weighted = 'weighted';
    case Uniform = 'uniform';
    case RoundRobin = 'round_robin';
}
