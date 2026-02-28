# UX baseline and fixes

## Audited surfaces
- Home (`front-page.php`): hero, expert block, logistics cards, CTA groups.
- Article (`single.php`): content flow, intent continuation, retention blocks.
- Calculator (`template-parts/quiz-calculator.php`): tabs, wizard, material modes, lead step.
- GEO dialog (`assets/src/main.js` + `footer.php`): booking flow and goals.

## Critical issues fixed
- Expert visual fallback on homepage now uses a real image fallback (no empty/emoji-only state).
- Calculator upgraded with scenario presets and package comparison to reduce drop-off.
- Contact step now supports phone **or** messenger plus explicit consent checkbox.
- Added context-aware calculator embedding for articles with default service prefill.
- Added progress points and step/result analytics events (`calc_open`, `calc_step_complete`, `calc_result_view`, `lead_submit`).

## Premium UX updates
- Added lightweight reveal animations on key cards.
- Added count-up for trust stats in hero with reduced-motion fallback.
- Introduced cleaner calculator UI components: presets, meta/progress row, package cards.

## SEO-safe notes
- No black-hat or cloaking patterns added.
- Changes improve intent completeness and engagement while preserving transparent lead flow.
