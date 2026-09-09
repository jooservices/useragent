<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

enum UniquePolicy: string
{
    case Fail = 'fail';
    case AllowDuplicates = 'allow_duplicates';
}
