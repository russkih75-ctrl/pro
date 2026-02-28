<?php
/**
 * Template: GEO page
 * /geo/{city}/ — обзор всех услуг в городе
 * /geo/{city}/{service}/ — конкретная услуга в городе
 * /geo/{city}/{service}/{intent}/ — услуга + интент (монтаж/цена/материалы)
 */
if (!defined('ABSPATH')) exit;

$ctx = kv_geo_get_context();
$city = $ctx['city'];
$region = $ctx['region'];
$service_data = $ctx['service'] ? kv_geo_find_service($ctx['service']) : null;
$intent_data  = $ctx['intent'] ? kv_geo_find_intent($ctx['intent']) : null;

if (!$city) {
  get_template_part('404');
  return;
}

$h1 = kv_geo_build_h1($ctx);
$city_name = $city['name'] ?? '';
$city_services = $city['services'] ?? [];
$photos = $city['photos'] ?? [];
$unique_facts = $city['unique_facts'] ?? [];
$delivery = $city['delivery_terms'] ?? '';
$payment = $city['payment_terms'] ?? '';
$offices = $city['offices'] ?? [];
$partner_points = function_exists('kv_geo_get_partner_points_for_city') ? kv_geo_get_partner_points_for_city((string)($ctx['city_slug'] ?? '')) : [];
$main_partner = function_exists('kv_geo_get_main_partner') ? kv_geo_get_main_partner() : null;
$population = $city['population'] ?? 0;
$distance = $city['distance_km'] ?? 0;

/**
 * Helpers for intent-driven blocks (AI-friendly Answer-First).
 */
function kv_geo_partner_intro_text(string $city_name, array $partner_points, ?array $main_partner, string $city_slug = ''): string {
  $city_name = trim($city_name);
  $city_slug = sanitize_title($city_slug);

  $area_hint = '';

  if ($partner_points) {
    $p0 = is_array($partner_points[0]) ? $partner_points[0] : [];
    $area_hint = trim((string)($p0['area_hint'] ?? ''));
  }

  // Stable variation by city slug to keep copy natural across GEO pages.
  $templates = [
    'В %city% есть пункт оформления заказов для просмотра образцов по записи. Точный адрес и маршрут отправим в Telegram после обращения.',
    'Нужен просмотр образцов в %city%? Подскажем ближайший пункт оформления заказов и отправим маршрут в Telegram.',
    '%city%: можно посмотреть образцы по записи. Напишите в Telegram — отправим адрес и маршрут.',
    'Для %city% доступен просмотр образцов по записи. Оформление заказа и поддержка — в Telegram.',
  ];

  $idx = 0;
  if ($city_slug !== '' && function_exists('crc32')) {
    $idx = abs((int)crc32($city_slug)) % count($templates);
  }
  $tpl = $templates[$idx] ?? $templates[0];

  $city = $city_name ?: 'городе';
  $hint = $area_hint ?: 'район/ориентир сообщим при подтверждении';

  return str_replace(['%city%','%area_hint%'], [$city, $hint], $tpl);
}

function kv_geo_tpl_replace(string $tpl, array $vars): string {
  foreach ($vars as $k => $v) {
    $tpl = str_replace('{' . $k . '}', (string)$v, $tpl);
  }
  return $tpl;
}

function kv_geo_render_quick_answer(array $ctx, array $city, ?array $service, ?array $intent, array $city_svc): void {
  if (!$service || !$intent) return;
  $city_name = $city['name'] ?? '';
  $price_from = $city_svc['price_from'] ?? '';
  $price_unit = $city_svc['price_unit'] ?? '';

  $vars = [
    'city' => $city_name,
    'year' => date('Y'),
    'service_name' => $service['name'] ?? '',
    'price_from' => $price_from,
    'price_unit' => $price_unit,
  ];

  $qa = $intent['qa_templates'][0] ?? null;
  if (!$qa || !is_array($qa)) return;

  $h2 = kv_geo_tpl_replace($qa['h2'] ?? '', $vars);
  $ans = kv_geo_tpl_replace($qa['answer_first'] ?? '', $vars);
  if (!$h2 || !$ans) return;
  ?>
  <section class="geo-block geo-block--quick-answer">
    <div class="container">
      <h2><?php echo esc_html($h2); ?></h2>
      <p class="geo-answer-first"><?php echo esc_html($ans); ?></p>
      <?php if (!empty($qa['detail_points']) && is_array($qa['detail_points'])): ?>
        <div class="geo-bullets">
          <?php foreach (array_slice($qa['detail_points'], 0, 6) as $p): ?>
            <p class="geo-bullet">— <?php echo esc_html($p); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <?php
}

function kv_geo_render_pricing_table(?array $service, string $city_name): void {
  if (!$service || empty($service['pricing_table']) || !is_array($service['pricing_table'])) return;
  ?>
  <section class="geo-block geo-block--pricing">
    <div class="container">
      <h2>Цены на <?php echo esc_html(mb_strtolower($service['name'] ?? '')); ?> в <?php echo esc_html($city_name); ?></h2>
      <div class="geo-table-wrap">
        <table class="geo-table">
          <thead>
            <tr>
              <th>Работа</th>
              <th>Цена от</th>
              <th>Примечание</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($service['pricing_table'], 0, 12) as $row): ?>
              <?php if (!is_array($row)) continue; ?>
              <tr>
                <td><?php echo esc_html($row['name'] ?? ''); ?></td>
                <td>
                  <?php if (!empty($row['from'])): ?>
                    <?php echo esc_html($row['from']); ?> <?php echo esc_html($row['unit'] ?? ''); ?>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td><?php echo esc_html($row['note'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_render_comparison_table(?array $service, string $city_name): void {
  if (!$service || empty($service['comparison_table']) || !is_array($service['comparison_table'])) return;
  $materials = [];
  foreach (($service['materials'] ?? []) as $m) {
    if (is_array($m) && !empty($m['slug'])) $materials[$m['slug']] = $m;
  }
  ?>
  <section class="geo-block geo-block--compare">
    <div class="container">
      <h2>Сравнение материалов — <?php echo esc_html(mb_strtolower($service['name'] ?? '')); ?> в <?php echo esc_html($city_name); ?></h2>
      <div class="geo-table-wrap">
        <table class="geo-table">
          <thead>
            <tr>
              <th>Материал</th>
              <th>Ценовой уровень</th>
              <th>Срок службы</th>
              <th>Подходит для</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($service['comparison_table'], 0, 8) as $row): ?>
              <?php if (!is_array($row)) continue; ?>
              <?php $ms = (string)($row['material_slug'] ?? ''); ?>
              <tr>
                <td><?php echo esc_html($materials[$ms]['name'] ?? $ms); ?></td>
                <td><?php echo esc_html($row['price_level'] ?? ''); ?></td>
                <td><?php echo esc_html($row['durability_years'] ?? ''); ?></td>
                <td><?php echo esc_html($row['best_for'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_render_process_steps(?array $service): void {
  if (!$service || empty($service['process_steps']) || !is_array($service['process_steps'])) return;
  ?>
  <section class="geo-block geo-block--steps">
    <div class="container">
      <h2>Как мы работаем</h2>
      <div class="geo-steps">
        <?php foreach (array_slice($service['process_steps'], 0, 8) as $step): ?>
          <?php if (!is_array($step)) continue; ?>
          <div class="geo-step card">
            <h3><?php echo esc_html($step['title'] ?? ''); ?></h3>
            <p><?php echo esc_html($step['text'] ?? ''); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_render_semantic_queries(?array $region, ?array $service, string $intent_slug): void {
  if (!$region || !$service) return;
  if (!function_exists('kv_geo_semantic_pick_entry') || !function_exists('kv_geo_semantic_top_queries')) return;

  $region_slug = (string)($region['slug'] ?? '');
  $service_slug = (string)($service['slug'] ?? '');
  if (!$region_slug || !$service_slug) return;

  $entry = kv_geo_semantic_pick_entry($region_slug, $service_slug, $intent_slug);
  if (!$entry) return;

  $queries = kv_geo_semantic_top_queries($entry, $region_slug, 8);
  if (!$queries) return;

  $region_name = (string)($region['name'] ?? '');
  $service_name = (string)($service['name'] ?? '');
  $stage = (string)($entry['stage'] ?? '');

  $stage_labels = [
    'problem' => 'проблемы',
    'repair' => 'ремонта',
    'solution' => 'решения',
    'materials' => 'материалов',
    'compare' => 'сравнения',
    'price' => 'цены',
    'calculator' => 'расчёта',
    'installation' => 'монтажа',
    'contractor' => 'подрядчика',
    'turnkey' => 'под ключ',
  ];
  $stage_label = $stage_labels[$stage] ?? 'темы';
  ?>
  <section class="geo-block geo-block--semantic">
    <div class="container">
      <h2>Что ищут в <?php echo esc_html($region_name ?: 'регионе'); ?> по теме «<?php echo esc_html($service_name); ?>»</h2>
      <p class="geo-semantic-sub">Подборка реальных запросов из поиска (кластер: <?php echo esc_html($stage_label); ?>) — помогает быстро понять, что важно людям.</p>
      <div class="geo-semantic-grid">
        <?php foreach ($queries as $row): ?>
          <div class="geo-semantic-item card">
            <div class="geo-semantic-q"><?php echo esc_html($row['query']); ?></div>
            <?php if (!empty($row['shows'])): ?>
              <div class="geo-semantic-s text-muted"><?php echo esc_html(number_format((int)$row['shows'], 0, '', ' ')); ?> показов/мес.</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_render_semantic_intent_cards(?array $region, ?array $service, string $city_slug): void {
  if (!$region || !$service) return;
  if (!function_exists('kv_geo_semantic_pick_entry') || !function_exists('kv_geo_semantic_top_queries')) return;

  $region_slug = (string)($region['slug'] ?? '');
  $service_slug = (string)($service['slug'] ?? '');
  if (!$region_slug || !$service_slug || !$city_slug) return;

  $intent_slugs = ['remont', 'materialy', 'tsena', 'montazh', 'pod-klyuch', 'kalkulyator'];
  $intent_map = [];
  foreach (kv_geo_get_intents() as $i) {
    if (is_array($i) && !empty($i['slug'])) $intent_map[$i['slug']] = $i;
  }

  ?>
  <section class="geo-block geo-block--semantic">
    <div class="container">
      <h2>Выберите задачу — и сразу попадите на нужный ответ</h2>
      <p class="geo-semantic-sub">Под каждую задачу есть отдельная страница: ремонт, материалы, цены, монтаж, под ключ, калькулятор. Ниже — реальные запросы из поиска по области.</p>

      <div class="geo-semantic-grid">
        <?php foreach ($intent_slugs as $intent_slug): ?>
          <?php
            $intent = $intent_map[$intent_slug] ?? null;
            if (!$intent) continue;
            $entry = kv_geo_semantic_pick_entry($region_slug, $service_slug, $intent_slug);
            $qs = $entry ? kv_geo_semantic_top_queries($entry, $region_slug, 2) : [];
            $url = home_url('/geo/' . $city_slug . '/' . $service_slug . '/' . $intent_slug . '/');
          ?>
          <a class="geo-semantic-item card card--link" href="<?php echo esc_url($url); ?>">
            <div class="geo-semantic-q"><?php echo esc_html($intent['name'] ?? $intent_slug); ?></div>
            <?php if ($qs): ?>
              <div class="text-muted" style="margin-top:8px;display:flex;flex-direction:column;gap:6px;">
                <?php foreach ($qs as $row): ?>
                  <span>• <?php echo esc_html($row['query']); ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="geo-semantic-s text-muted">Открыть страницу →</div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_render_paa_from_semantics(?array $region, ?array $service, string $intent_slug, array $vars): void {
  if (!$region || !$service || !$intent_slug) return;
  if (!function_exists('kv_geo_semantic_paa_items')) return;

  $region_slug = (string)($region['slug'] ?? '');
  $service_slug = (string)($service['slug'] ?? '');
  if (!$region_slug || !$service_slug) return;

  $items = kv_geo_semantic_paa_items($region_slug, $service_slug, $intent_slug, $vars, 6);
  if (!$items) return;

  ?>
  <section class="geo-block geo-block--paa">
    <div class="container">
      <h2>Вопросы из поиска — короткие ответы</h2>
      <p class="geo-semantic-sub">Мы собрали реальные формулировки запросов по области и дали короткие ответы в стиле «сразу по делу».</p>

      <div class="faq" itemscope itemtype="https://schema.org/FAQPage">
        <?php foreach ($items as $it): ?>
          <div class="faq-item animate-in" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <div class="faq-q faq-question" role="button" tabindex="0" aria-expanded="false" data-faq-toggle itemprop="name">
              <span><?php echo esc_html($it['q']); ?></span>
              <span class="faq-q__icon">+</span>
            </div>
            <div class="faq-a faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text"><?php echo esc_html($it['a']); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_render_paa_service_from_semantics(?array $region, ?array $service, array $vars): void {
  if (!$region || !$service) return;
  if (!function_exists('kv_geo_semantic_paa_items_service')) return;

  $region_slug = (string)($region['slug'] ?? '');
  $service_slug = (string)($service['slug'] ?? '');
  if (!$region_slug || !$service_slug) return;

  $items = kv_geo_semantic_paa_items_service($region_slug, $service_slug, $vars, 4);
  if (!$items) return;

  ?>
  <section class="geo-block geo-block--paa">
    <div class="container">
      <h2>Вопросы из поиска — короткие ответы</h2>
      <p class="geo-semantic-sub">Компактный блок по услуге: реальные запросы по области + короткие ответы «сразу по делу».</p>

      <div class="faq" itemscope itemtype="https://schema.org/FAQPage">
        <?php foreach ($items as $it): ?>
          <div class="faq-item animate-in" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <div class="faq-q faq-question" role="button" tabindex="0" aria-expanded="false" data-faq-toggle itemprop="name">
              <span><?php echo esc_html($it['q']); ?></span>
              <span class="faq-q__icon">+</span>
            </div>
            <div class="faq-a faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text"><?php echo esc_html($it['a']); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_render_city_paa_overview(?array $region, ?array $city): void {
  if (!$region || !$city) return;
  if (!function_exists('kv_geo_semantic_paa_items_service')) return;
  if (!function_exists('kv_geo_find_service')) return;

  $region_slug = (string)($region['slug'] ?? '');
  $city_name = (string)($city['name'] ?? '');
  if (!$region_slug || !$city_name) return;

  $city_prep = function_exists('kv_geo_ru_city_prep') ? kv_geo_ru_city_prep($city_name) : $city_name;
  $city_in = function_exists('kv_geo_ru_city_in') ? kv_geo_ru_city_in($city_name) : ('в ' . $city_prep);

  $service_slugs = ['krovlya', 'fasady', 'zabory'];
  ?>
  <section class="geo-block geo-block--paa">
    <div class="container">
      <h2>Что спрашивают в поиске по нашему профилю</h2>
      <p class="geo-semantic-sub">Короткие ответы по услугам <?php echo esc_html($city_in); ?> — чтобы быстрее понять, с чего начать.</p>
    </div>
  </section>
  <?php

  foreach ($service_slugs as $svc_slug) {
    $svc = kv_geo_find_service($svc_slug);
    if (!$svc) continue;
    $vars = [
      'city' => $city_name,
      'city_prep' => $city_prep,
      'city_in' => $city_in,
      'year' => date('Y'),
      'service_name' => (string)($svc['name'] ?? ''),
      'service_name_genitive' => (string)($svc['name_genitive'] ?? ($svc['name'] ?? '')),
      'service_name_accusative' => (string)($svc['name_accusative'] ?? ($svc['name'] ?? '')),
      'price_from' => '',
      'price_unit' => '',
    ];

    // if city has service price info, inject it
    if (!empty($city['services'][$svc_slug]) && is_array($city['services'][$svc_slug])) {
      $vars['price_from'] = (string)($city['services'][$svc_slug]['price_from'] ?? '');
      $vars['price_unit'] = (string)($city['services'][$svc_slug]['price_unit'] ?? '');
    }

    $items = kv_geo_semantic_paa_items_service($region_slug, $svc_slug, $vars, 2);
    if (!$items) continue;
    ?>
    <section class="geo-block geo-block--paa">
      <div class="container">
        <h3><?php echo esc_html($svc['name'] ?? 'Услуга'); ?></h3>
        <div class="faq" itemscope itemtype="https://schema.org/FAQPage">
          <?php foreach ($items as $it): ?>
            <div class="faq-item animate-in" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
              <div class="faq-q faq-question" role="button" tabindex="0" aria-expanded="false" data-faq-toggle itemprop="name">
                <span><?php echo esc_html($it['q']); ?></span>
                <span class="faq-q__icon">+</span>
              </div>
              <div class="faq-a faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                <div itemprop="text"><?php echo esc_html($it['a']); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
  }
}

function kv_geo_render_city_semantic_overview(?array $region, string $city_slug): void {
  if (!$region || !$city_slug) return;
  if (!function_exists('kv_geo_semantic_pick_entry') || !function_exists('kv_geo_semantic_top_queries')) return;

  $region_slug = (string)($region['slug'] ?? '');
  if (!$region_slug) return;

  $services = kv_geo_get_services();
  if (!is_array($services) || !$services) return;

  ?>
  <section class="geo-block geo-block--semantic">
    <div class="container">
      <h2>Что чаще всего ищут в <?php echo esc_html($region['name'] ?? 'регионе'); ?></h2>
      <p class="geo-semantic-sub">Это помогает понять типовые боли и вопросы по кровле, фасадам и заборам. Мы собрали данные по области и разложили по услугам.</p>

      <div class="geo-semantic-grid">
        <?php foreach ($services as $svc): ?>
          <?php
            if (!is_array($svc)) continue;
            $svc_slug = (string)($svc['slug'] ?? '');
            if (!$svc_slug) continue;
            $entry = kv_geo_semantic_pick_entry($region_slug, $svc_slug, 'remont');
            $qs = $entry ? kv_geo_semantic_top_queries($entry, $region_slug, 3) : [];
            $url = home_url('/geo/' . $city_slug . '/' . $svc_slug . '/');
          ?>
          <a class="geo-semantic-item card card--link" href="<?php echo esc_url($url); ?>">
            <div class="geo-semantic-q"><?php echo esc_html($svc['name'] ?? $svc_slug); ?></div>
            <?php if ($qs): ?>
              <div class="text-muted" style="margin-top:8px;display:flex;flex-direction:column;gap:6px;">
                <?php foreach ($qs as $row): ?>
                  <span>• <?php echo esc_html($row['query']); ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="geo-semantic-s text-muted">Открыть →</div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php
}

function kv_geo_next_intent_slug(string $current): string {
  // Funnel sequence: remont -> materialy -> tsena -> montazh -> pod-klyuch -> kalkulyator (then cycle)
  $seq = ['remont', 'materialy', 'tsena', 'montazh', 'pod-klyuch', 'kalkulyator'];
  $idx = array_search($current, $seq, true);
  if ($idx === false) return $seq[0];
  return $seq[($idx + 1) % count($seq)];
}

get_header();
?>

<div class="geo-page">
  <!-- Breadcrumbs -->
  <nav class="breadcrumbs" aria-label="Навигация">
    <div class="container">
      <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
      <span class="sep">→</span>
      <?php if ($region): ?>
        <span><?php echo esc_html($region['name']); ?></span>
        <span class="sep">→</span>
      <?php endif; ?>
      <?php if ($service_data || $intent_data): ?>
        <a href="<?php echo esc_url(home_url('/geo/' . $ctx['city_slug'] . '/')); ?>"><?php echo esc_html($city_name); ?></a>
        <span class="sep">→</span>
      <?php endif; ?>
      <?php if ($service_data && $intent_data): ?>
        <a href="<?php echo esc_url(home_url('/geo/' . $ctx['city_slug'] . '/' . $ctx['service'] . '/')); ?>"><?php echo esc_html($service_data['name']); ?></a>
        <span class="sep">→</span>
        <span><?php echo esc_html($intent_data['name']); ?></span>
      <?php elseif ($service_data): ?>
        <span><?php echo esc_html($service_data['name']); ?></span>
      <?php else: ?>
        <span><?php echo esc_html($city_name); ?></span>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Hero section -->
  <section class="geo-hero">
    <div class="container">
      <h1><?php echo esc_html($h1); ?></h1>
      <div class="geo-hero__meta">
        <?php if ($population): ?>
          <span class="geo-badge">👥 <?php echo number_format($population, 0, '', ' '); ?> чел.</span>
        <?php endif; ?>
        <?php if ($distance > 0): ?>
          <span class="geo-badge">📍 <?php echo $distance; ?> км от Борисоглебска</span>
        <?php endif; ?>
        <?php if ($delivery): ?>
          <span class="geo-badge">🚛 <?php echo esc_html($delivery); ?></span>
        <?php endif; ?>
      </div>
      <div class="geo-hero__cta">
        <a href="#calculator" class="btn btn--primary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-4px;"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="14.01"></line><line x1="12" y1="14" x2="12" y2="14.01"></line><line x1="8" y1="14" x2="8" y2="14.01"></line><line x1="16" y1="18" x2="16" y2="18.01"></line><line x1="12" y1="18" x2="12" y2="18.01"></line><line x1="8" y1="18" x2="8" y2="18.01"></line></svg> Рассчитать стоимость
        </a>
        <a href="tel:<?php echo esc_attr(function_exists('kv_phone') ? kv_phone(false) : ''); ?>" class="btn btn--outline">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg> Позвонить
        </a>
      </div>
    </div>
  </section>

  <?php if (!$service_data): ?>
  <!-- City overview: all services -->
  <section class="geo-services">
    <div class="container">
      <h2>Наши услуги в <?php echo esc_html($city_name); ?></h2>
      <div class="geo-services__grid">
        <?php
        $all_services = kv_geo_get_services();
        foreach ($all_services as $svc):
          $svc_slug = $svc['slug'];
          $city_svc = $city_services[$svc_slug] ?? null;
          $price_from = $city_svc['price_from'] ?? '—';
          $price_unit = $city_svc['price_unit'] ?? '';
          $popular = $city_svc['popular_materials'] ?? [];
        ?>
        <div class="geo-service-card animate-in">
          <h3><a href="<?php echo esc_url(home_url('/geo/' . $ctx['city_slug'] . '/' . $svc_slug . '/')); ?>"><?php echo esc_html($svc['name']); ?></a></h3>
          <div class="geo-service-card__price">от <?php echo esc_html($price_from); ?> <?php echo esc_html($price_unit); ?></div>
          <?php if ($popular): ?>
            <div class="geo-service-card__materials">
              <?php foreach ($popular as $mat): ?>
                <span class="tag"><?php echo esc_html($mat); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="geo-service-card__links">
            <?php
            $intents = kv_geo_get_intents();
            foreach (array_slice($intents, 0, 3) as $int):
            ?>
              <a href="<?php echo esc_url(home_url('/geo/' . $ctx['city_slug'] . '/' . $svc_slug . '/' . $int['slug'] . '/')); ?>"><?php echo esc_html($int['name']); ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php kv_geo_render_city_paa_overview($region, $city); ?>
  <?php kv_geo_render_city_semantic_overview($region, $ctx['city_slug']); ?>
  <?php endif; ?>

  <?php if ($service_data): ?>
  <!-- Service detail for this city -->
  <section class="geo-service-detail">
    <div class="container">
      <?php
      $svc_slug = $ctx['service'];
      $city_svc = $city_services[$svc_slug] ?? null;
      $price_from = $city_svc['price_from'] ?? '—';
      $price_unit = $city_svc['price_unit'] ?? '';
      $popular = $city_svc['popular_materials'] ?? [];
      ?>

      <?php
        $cityNom = (string)($city['name'] ?? $city_name);
        $cityPrep = function_exists('kv_geo_ru_city_prep') ? kv_geo_ru_city_prep($cityNom) : $cityNom;
        $cityIn = function_exists('kv_geo_ru_city_in') ? kv_geo_ru_city_in($cityNom) : ('в ' . $cityPrep);
        $vars_common = [
          'city' => $cityNom,
          'city_prep' => $cityPrep,
          'city_in' => $cityIn,
          'year' => date('Y'),
          'service_name' => (string)($service_data['name'] ?? ''),
          'service_name_genitive' => (string)($service_data['name_genitive'] ?? ($service_data['name'] ?? '')),
          'service_name_accusative' => (string)($service_data['name_accusative'] ?? ($service_data['name'] ?? '')),
          'price_from' => (string)(is_array($city_svc) ? ($city_svc['price_from'] ?? '') : ''),
          'price_unit' => (string)(is_array($city_svc) ? ($city_svc['price_unit'] ?? '') : ''),
        ];
      ?>

      <?php if (!$intent_data): ?>
      <!-- Intent navigation -->
      <div class="geo-intents">
        <?php foreach (kv_geo_get_intents() as $int): ?>
          <a href="<?php echo esc_url(home_url('/geo/' . $ctx['city_slug'] . '/' . $svc_slug . '/' . $int['slug'] . '/')); ?>" class="geo-intent-link <?php echo $ctx['intent'] === $int['slug'] ? 'active' : ''; ?>">
            <?php echo esc_html($int['name']); ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Price block -->
      <div class="geo-price-block">
        <div class="geo-price-block__main">
          <span class="geo-price-label"><?php echo esc_html($service_data['name']); ?> в <?php echo esc_html($city_name); ?></span>
          <span class="geo-price-value">от <?php echo esc_html($price_from); ?> <?php echo esc_html($price_unit); ?></span>
        </div>
        <p class="geo-price-note">Цена включает: материалы + доставка + монтаж. Точный расчёт — после замера.</p>
      </div>

      <?php
        // Intent-driven blocks (only for /geo/city/service/intent/)
        if ($intent_data && is_array($intent_data)) {
          kv_geo_render_quick_answer($ctx, $city, $service_data, $intent_data, is_array($city_svc) ? $city_svc : []);
          kv_geo_render_semantic_queries($region, $service_data, (string)$ctx['intent']);

          kv_geo_render_paa_from_semantics($region, $service_data, (string)$ctx['intent'], $vars_common);

          $blocks = $intent_data['blocks'] ?? [];
          if (!is_array($blocks)) $blocks = [];

          $cityName = $city_name;
          if (in_array('pricing_table', $blocks, true)) {
            kv_geo_render_pricing_table($service_data, $cityName);
          }
          if (in_array('comparison_table', $blocks, true)) {
            kv_geo_render_comparison_table($service_data, $cityName);
          }
          if (in_array('process_steps', $blocks, true)) {
            kv_geo_render_process_steps($service_data);
          }

          // Next step (funnel internal link)
          $nextIntent = kv_geo_next_intent_slug((string)$ctx['intent']);
          $nextIntentData = kv_geo_find_intent($nextIntent);
          if ($nextIntentData) {
            $nextUrl = home_url('/geo/' . $ctx['city_slug'] . '/' . $svc_slug . '/' . $nextIntent . '/');
            ?>
            <section class="geo-block geo-block--next">
              <div class="container">
                <div class="card">
                  <h2>Следующий шаг</h2>
                  <p>Продолжите путь: <a href="<?php echo esc_url($nextUrl); ?>"><?php echo esc_html($nextIntentData['name'] ?? ''); ?></a></p>
                </div>
              </div>
            </section>
            <?php
          }
        }
      ?>

      <?php if (!$intent_data): ?>
        <?php kv_geo_render_semantic_intent_cards($region, $service_data, $ctx['city_slug']); ?>
        <?php kv_geo_render_paa_service_from_semantics($region, $service_data, $vars_common); ?>
      <?php endif; ?>

      <!-- Materials -->
      <?php if (!empty($service_data['materials'])): ?>
      <div class="geo-materials">
        <h2>Материалы для <?php echo esc_html(mb_strtolower($service_data['name_genitive'] ?? $service_data['name'])); ?></h2>
        <div class="geo-materials__grid">
          <?php foreach ($service_data['materials'] as $mat): ?>
          <div class="geo-material-card">
            <h3><?php echo esc_html($mat['name']); ?></h3>
            <p><?php echo esc_html($mat['description']); ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- FAQ -->
      <?php if (!empty($service_data['faq'])): ?>
      <div class="geo-faq">
        <h2>Частые вопросы — <?php echo esc_html(mb_strtolower($service_data['name'])); ?> в <?php echo esc_html($city_name); ?></h2>
        <div class="faq" itemscope itemtype="https://schema.org/FAQPage">
          <?php foreach ($service_data['faq'] as $faq): ?>
          <div class="faq-item animate-in" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <div class="faq-q faq-question" role="button" tabindex="0" aria-expanded="false" data-faq-toggle itemprop="name">
              <span><?php echo esc_html($faq['q']); ?></span>
              <span class="faq-q__icon">+</span>
            </div>
            <div class="faq-a faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text"><?php echo esc_html($faq['a']); ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Unique facts about working in this city -->
  <?php if ($unique_facts): ?>
  <section class="geo-facts">
    <div class="container">
      <h2>Почему выбирают нас в <?php echo esc_html($city_name); ?></h2>
      <div class="geo-facts__grid">
        <?php foreach ($unique_facts as $i => $fact): ?>
        <div class="geo-fact">
          <span class="geo-fact__num"><?php echo $i + 1; ?></span>
          <p><?php echo esc_html($fact); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Delivery and payment -->
  <section class="geo-logistics">
    <div class="container">
      <h2>Доставка и оплата — <?php echo esc_html($city_name); ?></h2>
      <div class="geo-logistics__grid">
        <div class="geo-logistics__card animate-in">
          <h3>🚛 Доставка</h3>
          <p><?php echo esc_html($delivery); ?></p>
          <?php if (!empty($city['delivery_days_min']) && !empty($city['delivery_days_max'])): ?>
            <p class="geo-logistics__days">Срок: <?php echo $city['delivery_days_min']; ?>–<?php echo $city['delivery_days_max']; ?> рабочих дней</p>
          <?php endif; ?>
        </div>
        <div class="geo-logistics__card animate-in">
          <h3>💳 Оплата</h3>
          <p><?php echo esc_html($payment); ?></p>
        </div>
        <?php if ($partner_points || $main_partner): ?>
        <div class="geo-logistics__card animate-in">
          <h3>🏪 Пункт оформления заказов — <?php echo esc_html($city_name); ?></h3>
          <p><?php echo esc_html(kv_geo_partner_intro_text((string)$city_name, (array)$partner_points, is_array($main_partner) ? $main_partner : null, (string)($ctx['city_slug'] ?? ''))); ?></p>

          <?php if ($partner_points): ?>
            <?php foreach ($partner_points as $pt): ?>
              <?php
                if (!is_array($pt)) continue;
                $pt_id = (string)($pt['id'] ?? '');
                $pt_name = (string)($pt['name'] ?? '');
                $pt_addr = (string)($pt['address'] ?? '');
                $pt_area_hint = (string)($pt['area_hint'] ?? '');
                $pt_url = (string)($pt['route_url'] ?? $pt['url'] ?? '');
                $pt_partner_id = (string)($pt['partner_id'] ?? '');
                $pt_rel = (string)($pt['link_rel'] ?? 'nofollow noopener noreferrer');
                if (!$pt_url) continue;
              ?>
              <div class="kv-partner-point">
                <p style="margin:0 0 10px;">
                  <strong><?php echo esc_html($pt_name ?: 'Пункт оформления заказов'); ?></strong><br>
                  <span class="text-muted">Ориентир: <?php echo esc_html($pt_area_hint ?: 'район уточним при подтверждении записи'); ?></span><br>
                  <span class="text-muted">Точный адрес и маршрут отправим после записи.</span>
                </p>
                <div class="btn-group" style="gap:10px;flex-wrap:wrap;">
                  <button
                    type="button"
                    class="btn btn--primary btn--sm"
                    data-kv-partner-book
                    data-route-url="<?php echo esc_url($pt_url); ?>"
                    data-point-id="<?php echo esc_attr($pt_id); ?>"
                    data-city="<?php echo esc_attr($city_name); ?>"
                    data-city-slug="<?php echo esc_attr((string)($ctx['city_slug'] ?? '')); ?>"
                    data-region-slug="<?php echo esc_attr((string)($city['region'] ?? '')); ?>"
                    data-service-slug="<?php echo esc_attr((string)($ctx['service'] ?? '')); ?>"
                    data-area-hint="<?php echo esc_attr($pt_area_hint); ?>"
                  >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M9 16l2 2 4-4"></path></svg> Получить адрес в Telegram</button>
                  <a
                    href="<?php echo esc_url($pt_url); ?>"
                    class="btn btn--sm"
                    rel="<?php echo esc_attr($pt_rel); ?>"
                    data-kv-partner-open
                    data-route-url="<?php echo esc_url($pt_url); ?>"
                    data-point-id="<?php echo esc_attr($pt_id); ?>"
                    data-city="<?php echo esc_attr($city_name); ?>"
                    data-city-slug="<?php echo esc_attr((string)($ctx['city_slug'] ?? '')); ?>"
                    data-region-slug="<?php echo esc_attr((string)($city['region'] ?? '')); ?>"
                    data-service-slug="<?php echo esc_attr((string)($ctx['service'] ?? '')); ?>"
                    data-area-hint="<?php echo esc_attr($pt_area_hint); ?>"
                  >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><circle cx="12" cy="10" r="3"></circle><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"></path></svg> Маршрут в Яндекс.Картах</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <?php
              $mp_name = is_array($main_partner) ? (string)($main_partner['name'] ?? '') : '';
              $mp_url = is_array($main_partner) ? (string)($main_partner['site_url'] ?? '') : '';
              $mp_rel = is_array($main_partner) ? (string)($main_partner['link_rel_default'] ?? 'nofollow noopener noreferrer') : 'nofollow noopener noreferrer';
            ?>
            <?php if ($mp_url): ?>
              <div class="btn-group" style="gap:10px;flex-wrap:wrap;">
                <button
                  type="button"
                  class="btn btn--primary btn--sm"
                  data-kv-partner-book
                  data-route-url="<?php echo esc_url($mp_url); ?>"
                  data-point-id="main"
                  data-city="<?php echo esc_attr($city_name); ?>"
                  data-city-slug="<?php echo esc_attr((string)($ctx['city_slug'] ?? '')); ?>"
                  data-region-slug="<?php echo esc_attr((string)($city['region'] ?? '')); ?>"
                  data-service-slug="<?php echo esc_attr((string)($ctx['service'] ?? '')); ?>"
                  data-area-hint=""
                >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M9 16l2 2 4-4"></path></svg> Получить адрес в Telegram</button>
                <a
                  href="<?php echo esc_url($mp_url); ?>"
                  class="btn btn--sm"
                  rel="<?php echo esc_attr($mp_rel); ?>"
                  data-kv-partner-open
                  data-route-url="<?php echo esc_url($mp_url); ?>"
                  data-point-id="main"
                  data-city="<?php echo esc_attr($city_name); ?>"
                  data-city-slug="<?php echo esc_attr((string)($ctx['city_slug'] ?? '')); ?>"
                  data-region-slug="<?php echo esc_attr((string)($city['region'] ?? '')); ?>"
                  data-service-slug="<?php echo esc_attr((string)($ctx['service'] ?? '')); ?>"
                  data-area-hint=""
                >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><circle cx="12" cy="10" r="3"></circle><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"></path></svg> Маршрут в Яндекс.Картах</a>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Quiz Calculator -->
  <section class="geo-calculator">
    <div class="container">
      <?php get_template_part('template-parts/quiz-calculator'); ?>
    </div>
  </section>

  <!-- Internal links to nearby cities -->
  <section class="geo-nearby">
    <div class="container">
      <h2>Работаем также в</h2>
      <div class="geo-nearby__links">
        <?php
        $all_cities = kv_geo_get_cities();
        $nearby = [];
        foreach ($all_cities as $c) {
          if ($c['slug'] === $ctx['city_slug']) continue;
          if (($c['region'] ?? '') === ($city['region'] ?? '')) {
            $nearby[] = $c;
          }
        }
        // Also add cities from other regions if < 8
        if (count($nearby) < 8) {
          foreach ($all_cities as $c) {
            if ($c['slug'] === $ctx['city_slug']) continue;
            if (($c['region'] ?? '') !== ($city['region'] ?? '')) {
              $nearby[] = $c;
              if (count($nearby) >= 12) break;
            }
          }
        }
        foreach (array_slice($nearby, 0, 12) as $nc):
          $link = '/geo/' . $nc['slug'] . '/';
          if ($ctx['service']) $link .= $ctx['service'] . '/';
          if ($ctx['intent']) $link .= $ctx['intent'] . '/';
        ?>
          <a href="<?php echo esc_url(home_url($link)); ?>" class="geo-nearby__link">
            <?php echo esc_html($nc['name']); ?>
            <?php if (!empty($nc['distance_km'])): ?>
              <small>(<?php echo $nc['distance_km']; ?> км)</small>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Next Action Block -->
  <section class="next-action animate-in" style="margin-top:64px;padding:48px 0;background:var(--bg-alt);">
    <div class="container text-center">
        <h2 style="font-size:32px;font-weight:800;margin-bottom:16px;">Нужна помощь с выбором в <?php echo esc_html($city_name); ?>?</h2>
        <p style="color:var(--text-secondary);margin-bottom:32px;max-width:600px;margin-left:auto;margin-right:auto;">
            Оставьте контакт для связи, и мы организуем просмотр образцов или сделаем точный расчёт сметы с учётом логистики.
        </p>
        <div class="btn-group" style="justify-content:center;flex-wrap:wrap;gap:16px;">
            <a href="#calculator" class="btn btn--primary btn--lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-4px;"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="14.01"></line><line x1="12" y1="14" x2="12" y2="14.01"></line><line x1="8" y1="14" x2="8" y2="14.01"></line><line x1="16" y1="18" x2="16" y2="18.01"></line><line x1="12" y1="18" x2="12" y2="18.01"></line><line x1="8" y1="18" x2="8" y2="18.01"></line></svg>
                Рассчитать стоимость
            </a>
            <a href="tel:<?php echo esc_attr(function_exists('kv_phone') ? kv_phone(false) : ''); ?>" class="btn btn--outline btn--lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                Связаться с нами
            </a>
        </div>
    </div>
  </section>

</div><!-- .geo-page -->

<?php get_footer(); ?>
