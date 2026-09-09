<?php

declare(strict_types=1);

namespace JOOservices\UserAgent;

use Closure;
use JOOservices\UserAgent\Core\BatchGenerator;
use JOOservices\UserAgent\Core\CompatibilityResolver;
use JOOservices\UserAgent\Core\GenerationSession;
use JOOservices\UserAgent\Core\ProfileSelector;
use JOOservices\UserAgent\Core\Renderer;
use JOOservices\UserAgent\Dataset\BundledDatasetRepository;
use JOOservices\UserAgent\Dataset\Dataset;
use JOOservices\UserAgent\Domain\BatchResult;
use JOOservices\UserAgent\Domain\CompatibilityTuple;
use JOOservices\UserAgent\Domain\DatasetProvenance;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\GenerationResult;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;
use JOOservices\UserAgent\Exceptions\RenderException;
use JOOservices\UserAgent\Facade\RequestBuilder;
use JOOservices\UserAgent\History\InMemoryHistoryStore;
use JOOservices\UserAgent\Random\MtRandomSource;

final class Generator
{
    /** @param Closure(?int): GenerationSession $sessionFactory */
    public function __construct(
        private readonly Dataset $dataset,
        private readonly CompatibilityResolver $compatibility,
        private readonly ProfileSelector $selector,
        private readonly Renderer $renderer,
        private readonly BatchGenerator $batch,
        private readonly Closure $sessionFactory,
        private readonly GeneratorConfig $config = new GeneratorConfig(),
    ) {
    }

    public static function create(?GeneratorConfig $config = null): self
    {
        $config ??= new GeneratorConfig();
        $dataset = (new BundledDatasetRepository())->load();
        $factory = static fn(?int $seed): GenerationSession => new GenerationSession(
            random: $seed === null ? new MtRandomSource() : MtRandomSource::seeded($seed),
            history: new InMemoryHistoryStore($config->historySize),
            seed: $seed,
        );

        return new self(
            dataset: $dataset,
            compatibility: new CompatibilityResolver(),
            selector: new ProfileSelector(),
            renderer: new Renderer(),
            batch: new BatchGenerator(),
            sessionFactory: $factory,
            config: $config,
        );
    }

    public function generate(GenerationRequest $request = new GenerationRequest()): GenerationResult
    {
        $session = ($this->sessionFactory)($request->seed);

        return $this->generateWithSession($request, $session);
    }

    public function generateMany(
        GenerationRequest $request,
        int $count,
        UniquePolicy $unique = UniquePolicy::Fail,
    ): BatchResult {
        if ($count < 1 || $count > 1000) {
            throw new InvalidRequestException('Batch count must be between 1 and 1000.');
        }
        $this->guardRevision($request);
        $session = ($this->sessionFactory)($request->seed);

        return $this->batch->generate(
            request: $request,
            count: $count,
            unique: $unique,
            attemptBudget: $unique === UniquePolicy::Fail ? $this->config->attemptBudget($count) : $count,
            session: $session,
            generateOne: $this->generateWithSession(...),
        );
    }

    /** @return list<CompatibilityTuple> */
    public function matrix(?GenerationRequest $filter = null): array
    {
        if ($filter !== null) {
            $this->guardRevision($filter);
        }

        return $this->compatibility->matrix($this->dataset, $filter);
    }

    public function provenance(): DatasetProvenance
    {
        return $this->dataset->provenance;
    }

    public function builder(): RequestBuilder
    {
        return new RequestBuilder(generator: $this);
    }

    private function generateWithSession(GenerationRequest $request, GenerationSession $session): GenerationResult
    {
        $this->guardRevision($request);
        $tuples = $this->compatibility->filter($this->dataset, $request);
        $profile = $this->selector->select($this->dataset, $request, $session, $tuples);
        $key = implode('|', [$profile->browser->value, $profile->device->value, $profile->platform->value]);
        $template = $this->dataset->templates[$key] ?? null;
        if ($template === null) {
            throw new RenderException('The selected profile has no rendering template.');
        }
        $userAgent = $this->renderer->render($template, $profile);

        return new GenerationResult(
            userAgent: $userAgent,
            profile: $profile,
            provenance: $this->dataset->provenance,
            seed: $session->seed,
            selection: $request->selection,
            candidateCount: count($tuples),
        );
    }

    private function guardRevision(GenerationRequest $request): void
    {
        if ($request->datasetRevision !== null && $request->datasetRevision !== $this->dataset->provenance->revision) {
            throw new InvalidRequestException('The requested dataset revision is not loaded.');
        }
    }
}
