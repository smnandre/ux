# CHANGELOG

## Unreleased

- Add numbered, lookahead and bidirectional cursor pagination behind one
  request-aware `PaginatorInterface` service.
- Add adapters for arrays, Doctrine ORM 3, Doctrine DBAL 4.4+ and
  callback-backed application sources.
- Add signed, opaque cursor tokens bound to the effective order, source query
  and application context, with automatic Doctrine identifier tie-breakers.
- Add immutable builders for page size, navigation windows, URL composition,
  cursor ordering and application-provided lazy totals.
- Add named paginator profiles with Symfony argument-name, `#[Target]` and
  explicit service injection conventions.
- Add the `ux_pagination()` Twig function, `<twig:ux:pagination>` component,
  translated summaries, default, Bootstrap and Tailwind templates, structural
  blocks and validated HTML attribute extension points.
- Add JSON serialization with consistent `prev`/`next` links and public result
  contracts for common, numbered and cursor pagination state.
- Add optional LiveComponent integration through `ComponentWithPaginationTrait`, including
  real-link fallbacks and route-path page synchronization.
- Add `Test\PaginatorFactory` for deterministic application tests without a
  PHPUnit dependency.
- Preserve request filters by default, reject malformed page and cursor input,
  guard excessive offsets and keep canonical/redirect policy in the
  application.
- Keep pagination functional without a Stimulus or Turbo runtime.
