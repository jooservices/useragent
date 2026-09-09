# Upgrade to 4.0

Version 4 is a ground-up rebuild. There is no 1.x compatibility layer.

The common fluent shape remains:

```php
UserAgent::chrome()->windows()->desktop()->generate();
UserAgent::builder()->mobile()->android()->locale('en-US')->recent(6)->generate();
```

`GenerationSpec` is removed. Use the immutable `GenerationRequest`. Strategy
class strings are replaced by `SelectionPolicyId`; static uniqueness state and
bot methods are removed. `Generator::generate()` now returns the complete
`GenerationResult`; only the fluent facade returns a string directly.
