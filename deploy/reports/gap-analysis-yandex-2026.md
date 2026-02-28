# GAP Analysis — Yandex 2026 (White + Safe Gray)

## Baseline

- `npm test`: 8/8 passed.
- `npm run audit:benchmark`: passed.
- `npm run audit:server`: failed due DNS (`ftp.kvadratyra.ru` not resolved in current environment).

## Critical

- FTP docroot/drift checks are blocked by hostname resolution and currently do not validate real server state.
- Generic canonical fallback is missing for non-GEO pages when SEO plugins are absent.
- Dynamic `robots_txt` output in theme differs from static `robots.txt` policy, which can lead to config drift.

## High

- HTTP audit currently checks only a small SEO subset (title/description/canonical/robots/json-ld count).
- Trust-page seeding does not include "О компании" and "Вакансии", while commercial-trust signals require stronger company presence.
- Homepage schema comment mentions FAQPage, but FAQPage block is not emitted.

## Medium

- HTTP audit output does not include indexability flags and canonical consistency checks.
- URL set in server audit can be expanded with core trust/commercial pages and dzen feed endpoint.

## Planned Implementation

1. Add canonical fallback for non-GEO pages and align robots policy.
2. Expand trust-page auto-seeding (about/vacancies) and homepage schema FAQ block.
3. Extend HTTP SEO extraction + reports (OG/Twitter/indexability/canonical checks).
4. Expand audited URL matrix and regenerate reports.
