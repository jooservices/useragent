# UserAgent v4 architecture

`GenerationRequest` is the only input contract. `Generator` creates one
`GenerationSession` per single request or batch, filters the checksummed dataset
through `CompatibilityResolver`, selects a coherent profile with one random
source, and passes that profile to `Renderer`. The facade and CLI only map input
to this engine.

The bundled schema is version 1 and consists of compatibility, browser release,
platform, model, and template files. Its manifest checksum is SHA-256 over those
raw files in that exact order. JSON is data, never executable PHP, and no public
API accepts a path.

Public payloads extend `JOOservices\Dto\Core\Dto`. Runtime ports are limited to
randomness, history, dataset repository, and selection policy. Selection is a
closed registry: weighted, uniform, or generator-owned round robin. Unique
batches share one session and fail closed at a bounded attempt limit.

Bundled templates do not render locale; it remains explicit profile metadata.
ChromeOS and iPad are first-class dataset tuples. Bots, parsing, custom paths,
channels, arbitrary policy classes, and process-global state are outside 4.0.
