# Yandex 2026 Revision Baseline

Date: 2026-02-19
Scope: logic, architecture, navigation, SEO/GEO, content completeness, intent fit.
Reference: https://mayai.ru/

## Critical

1. Partner showroom funnel was previously leaky (direct map open path).  
   Status: fixed in current build with booking-first route release.

2. Content/visual control for media IDs 24/25/26 required explicit refresh for premium style consistency.

## High

1. Front page has strong trust and logistics blocks, but lacks an explicit intent router
   (quick jumps for price / delivery / guarantee / geo / faq), which weakens neuro-answer flow.

2. GEO page partner block needed strict transparency copy:
   partner as showroom only, contract/payment/guarantee by Kvadratyra.
   Status: fixed in current build.

3. Metrika events were fragmented (`partner_*` mixed with other goals) and not normalized.
   Status: fixed in current build with `geo_*` naming.

## Medium

1. Single article template could better expose commercial relevance and city/service continuation links.
2. Homepage schema can be expanded with stronger answer-first and navigation entities.

## Next Fix Package

1. Front page: add compact intent navigation layer and answer-first shortcuts.
2. Single template: add city/service continuation CTA and tighter internal linking.
3. Schema graph/article: refine commercial and navigational entities.
4. Refresh media IDs 24/25/26 and assign to hero/service contexts.
