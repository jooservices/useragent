<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use Closure;
use JOOservices\UserAgent\Domain\BatchResult;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\GenerationResult;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Exceptions\UniqueGenerationExhausted;

final class BatchGenerator
{
    /** @param Closure(GenerationRequest, GenerationSession): GenerationResult $generateOne */
    public function generate(
        GenerationRequest $request,
        int $count,
        UniquePolicy $unique,
        int $attemptBudget,
        GenerationSession $session,
        Closure $generateOne,
    ): BatchResult {
        $entries = [];
        $attempts = 0;
        $produced = 0;
        while ($produced < $count && $attempts < $attemptBudget) {
            ++$attempts;
            $result = $generateOne($request, $session);
            if ($unique === UniquePolicy::Fail && $session->history->contains($result->userAgent)) {
                continue;
            }
            $entries[] = $result;
            ++$produced;
            $session->history->add($result->userAgent);
        }
        if ($produced < $count) {
            throw UniqueGenerationExhausted::forBatch($count, $produced, $attempts);
        }

        return new BatchResult(
            entries: $entries,
            requestedCount: $count,
            producedCount: $produced,
            attemptCount: $attempts,
            uniquePolicy: $unique,
        );
    }
}
