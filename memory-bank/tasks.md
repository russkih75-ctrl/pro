# Tasks — Yandex 2026 Full Revision Build

## Complexity
- Level: 2 (Simple enhancement touching GEO templates, JS UX flow, data model, and analytics naming)

## Goal
- Complete large revision for `kvadratyra.ru` based on Yandex 2026 principles and mayai-style structure:
  - logic + architecture + interface + navigation,
  - SEO/GEO + neuro-search answer-first improvements,
  - premium content and visuals refresh for critical materials/pages.

## Success Criteria
- [x] Baseline audit created with prioritized gaps.
- [x] IA/UI improvements deployed in key templates.
- [x] SEO/schema refinements deployed.
- [x] Premium commercial rewrites published for key posts.
- [x] Premium face-preserving visuals generated and published.
- [x] Build/tests/deploy completed successfully.
- [x] Server audit report confirms critical endpoints return 200.

## Implementation Checklist
- [x] Create baseline revision report (`deploy/reports/yandex-revision-baseline.md`).
- [x] Update `front-page.php` with intent-nav and answer-first section.
- [x] Update `single.php` with continuation GEO/commercial CTA.
- [x] Enhance `schema-graph.php` and `schema-article.php`.
- [x] Rewrite post content for IDs 4/5/6.
- [x] Generate and upload premium visuals (media IDs 63/64/65).
- [x] Assign premium visuals as featured images to posts 4/5/6.
- [x] Build theme assets and run test suite.
- [x] Deploy to live docroot and run server audit.

## Notes
- Execution performed directly in production mode per user request.
- Reference style benchmark used: https://mayai.ru/ (structure/readability cues only).
- Existing safe lead-first GEO funnel from previous step remained active and compatible with this revision.

## Premium UX + Calculator 2.0 (Phase 2)

### Goal
- Complete premium UX enhancement with expert-reference visuals, expanded calculator logic, and in-article retention blocks.

### Success Criteria
- [x] Homepage expert block always has a real photo fallback.
- [x] Calculator enhanced with presets, package comparison, progress points, and safer contact validation.
- [x] Article pages include context-aware expanded calculator block.
- [x] Analytics events added for calculator funnel (`calc_open`, `calc_step_complete`, `calc_result_view`, `lead_submit`).
- [x] Build, deploy, and server audit pass in production.

## Build Task — Blueprint to MCP Pipeline (Post + Guide)

## Complexity
- Level: 2 (Simple enhancement in automation flow + docs + content templates)

## Goal
- Использовать шаги и промпты из blueprint как единый источник для публикации:
  - сбор ключей через Wordstat MCP,
  - генерация обложек через Nano Banana MCP,
  - публикация в WordPress (post + guide) с поддержкой `publish`,
  - проверка попадания в `/feed/dzen/`.

## Success Criteria
- [x] В `automation/wp_autopost` есть извлечение промптов из blueprint в machine-readable формат.
- [x] Автопост умеет публиковать не только `post`, но и `guide` через `post_type`.
- [x] Есть готовый пример JSON для публикации `guide` со ссылками Avito/Telegram/телефон/MAX.
- [x] Есть пошаговый runbook до WordPress и проверки RSS Дзен.
- [x] Python-скрипты проходят синтаксическую проверку.

## Implementation Checklist
- [x] Обновить `automation/wp_autopost/wp_client.py` (поддержка `post_type`).
- [x] Добавить `automation/wp_autopost/extract_blueprint_prompts.py`.
- [x] Сгенерировать `automation/wp_autopost/out/blueprint-prompts.json` и `.../blueprint-runbook.md` из blueprint `(13)`.
- [x] Обновить `automation/wp_autopost/README.md` под новый пайплайн.
- [x] Добавить `automation/wp_autopost/articles/guide-example.json`.
- [x] Прогнать `python -m py_compile` для изменённых скриптов.

## Commands Executed
- `python -m py_compile "wp_client.py" "extract_blueprint_prompts.py"` -> OK
- `python "extract_blueprint_prompts.py" --blueprint "<Downloads/...blueprint (13).json>"` -> OK (артефакты в `automation/wp_autopost/out/`)

## Notes
- В текущей сессии прямые MCP tool-calls к Wordstat/NanoBanana/WordPress недоступны, поэтому реализован рабочий локальный build-пайплайн и артефакты для немедленного запуска публикации.
- Повторная проверка показала активный `mcp-kv`; выполнена фактическая публикация через MCP:
  - Wordstat: собран кластер по теме металлочерепицы в Воронежской области.
  - Nano Banana: сгенерированы 2 обложки.
  - WordPress: опубликованы `post` (ID 69) и `guide` (ID 70) в статусе `publish`.
  - RSS `/feed/dzen/`: подтверждено наличие MAX-ссылки из гайда.
