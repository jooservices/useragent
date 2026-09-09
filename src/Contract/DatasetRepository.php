<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contract;

use JOOservices\UserAgent\Dataset\Dataset;

interface DatasetRepository
{
    public function load(): Dataset;
}
