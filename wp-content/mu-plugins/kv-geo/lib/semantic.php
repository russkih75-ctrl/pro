<?php
if (!defined('ABSPATH')) exit;

/**
 * Semantic core helpers (Wordstat clusters)
 * Source file: data/semantic-core.json
 *
 * Goal:
 * - Pick the most relevant cluster for (region, service, intent)
 * - Provide "popular queries" for GEO pages to reduce thinness and match intent
 */

function kv_geo_semantic_data(): array {
  static $cache = null;
  if (is_array($cache)) return $cache;
  $cache = kv_geo_load_json('semantic-core.json');
  return is_array($cache) ? $cache : [];
}

function kv_geo_semantic_region_token(string $region_slug): string {
  $region_slug = sanitize_title($region_slug);
  $map = [
    'voronezhskaya-oblast' => 'воронеж',
    'tambovskaya-oblast'   => 'тамбов',
    'saratovskaya-oblast'  => 'саратов',
    'volgogradskaya-oblast'=> 'волгоград',
  ];
  return $map[$region_slug] ?? '';
}

function kv_geo_semantic_other_region_tokens(string $region_slug): array {
  $all = ['voronezhskaya-oblast','tambovskaya-oblast','saratovskaya-oblast','volgogradskaya-oblast'];
  $out = [];
  foreach ($all as $r) {
    if ($r === sanitize_title($region_slug)) continue;
    $t = kv_geo_semantic_region_token($r);
    if ($t) $out[] = $t;
  }
  return $out;
}

function kv_geo_semantic_preferred_stages_for_intent(string $intent_slug): array {
  $intent_slug = sanitize_title($intent_slug);
  switch ($intent_slug) {
    case 'remont':
      return ['problem', 'repair', 'solution'];
    case 'materialy':
      return ['materials', 'compare'];
    case 'tsena':
      return ['price', 'materials', 'solution'];
    case 'montazh':
      return ['installation', 'contractor'];
    case 'pod-klyuch':
      return ['turnkey', 'installation', 'price'];
    case 'kalkulyator':
      return ['calculator', 'price'];
    default:
      return ['solution', 'price', 'materials', 'compare', 'repair', 'problem'];
  }
}

/**
 * Return all semantic entries for a region+service.
 */
function kv_geo_semantic_entries(string $region_slug, string $service_slug): array {
  $region_slug = sanitize_title($region_slug);
  $service_slug = sanitize_title($service_slug);

  $data = kv_geo_semantic_data();
  $core = $data['core'] ?? [];
  if (!is_array($core)) return [];

  $out = [];
  foreach ($core as $e) {
    if (!is_array($e)) continue;
    if (($e['geo'] ?? '') !== $region_slug) continue;
    if (($e['service'] ?? '') !== $service_slug) continue;
    $out[] = $e;
  }
  return $out;
}

/**
 * Pick best semantic entry for (region, service, intent).
 * Prefer: entry.intent_mapping contains intent AND stage matches preferred order.
 */
function kv_geo_semantic_pick_entry(string $region_slug, string $service_slug, string $intent_slug = ''): ?array {
  $entries = kv_geo_semantic_entries($region_slug, $service_slug);
  if (!$entries) return null;

  $intent_slug = sanitize_title($intent_slug);
  if ($intent_slug) {
    $mapped = [];
    foreach ($entries as $e) {
      $map = $e['intent_mapping'] ?? [];
      if (!is_array($map)) continue;
      if (in_array($intent_slug, $map, true)) $mapped[] = $e;
    }

    if ($mapped) {
      $prefStages = kv_geo_semantic_preferred_stages_for_intent($intent_slug);
      foreach ($prefStages as $st) {
        foreach ($mapped as $e) {
          if (($e['stage'] ?? '') === $st) return $e;
        }
      }
      return $mapped[0];
    }
  }

  // Fallback: prefer "solution" then "price" then "materials"
  $fallback = ['solution', 'price', 'materials', 'compare', 'repair', 'problem', 'installation', 'calculator', 'turnkey'];
  foreach ($fallback as $st) {
    foreach ($entries as $e) {
      if (($e['stage'] ?? '') === $st) return $e;
    }
  }
  return $entries[0];
}

/**
 * Filter and return top queries for an entry.
 * Tries to avoid showing other regions/cities tokens (very rough heuristic).
 */
function kv_geo_semantic_top_queries(array $entry, string $region_slug, int $limit = 8): array {
  $top = $entry['top'] ?? [];
  if (!is_array($top)) return [];

  $region_token = kv_geo_semantic_region_token($region_slug);
  $other_tokens = kv_geo_semantic_other_region_tokens($region_slug);

  $out = [];
  foreach ($top as $row) {
    if (!is_array($row)) continue;
    $q = trim((string)($row['query'] ?? ''));
    $shows = (int)($row['shows'] ?? 0);
    if (!$q) continue;

    $ql = mb_strtolower($q);
    $skip = false;
    foreach ($other_tokens as $t) {
      if ($t && mb_strpos($ql, $t) !== false) { $skip = true; break; }
    }
    if ($skip) continue;

    // If query contains explicit region token, keep; if not, still keep (generic query).
    // But if it contains a *different* token we already filtered it out.
    $out[] = ['query' => $q, 'shows' => $shows];
    if (count($out) >= $limit) break;
  }

  // Ensure at least something
  if (!$out && is_array($top)) {
    foreach (array_slice($top, 0, $limit) as $row) {
      if (!is_array($row)) continue;
      $q = trim((string)($row['query'] ?? ''));
      $shows = (int)($row['shows'] ?? 0);
      if ($q) $out[] = ['query' => $q, 'shows' => $shows];
    }
  }

  return $out;
}

/* ===== PAA (People Also Ask) generation ===== */

function kv_geo_semantic_norm_space(string $s): string {
  $s = trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
  return $s;
}

function kv_geo_semantic_is_question(string $q): bool {
  $q = mb_strtolower(trim($q));
  if ($q === '') return false;
  if (mb_strpos($q, '?') !== false) return true;
  $starters = [
    'как ', 'сколько ', 'что ', 'чем ', 'где ', 'куда ', 'когда ', 'почему ', 'зачем ',
    'какой ', 'какая ', 'какие ', 'нужно ли ', 'можно ли ', 'кто ', 'кому ', 'от чего ',
  ];
  foreach ($starters as $s) {
    if (mb_strpos($q, $s) === 0) return true;
  }
  return false;
}

function kv_geo_semantic_ensure_qmark(string $q): string {
  $q = rtrim(trim($q), " \t\n\r\0\x0B");
  $q = rtrim($q, ' .!');
  if (!preg_match('/\?$/u', $q)) $q .= '?';
  // Capitalize first letter if possible
  $first = mb_substr($q, 0, 1);
  $rest  = mb_substr($q, 1);
  return mb_strtoupper($first) . $rest;
}

function kv_geo_semantic_strip_geo_tokens(string $q, string $region_slug, string $city_name): string {
  $q = kv_geo_semantic_norm_space($q);
  $ql = mb_strtolower($q);

  $region_token = kv_geo_semantic_region_token($region_slug);
  $city_token = mb_strtolower(trim($city_name));

  // Remove region token (if present as standalone word)
  if ($region_token) {
    $ql = preg_replace('/\b' . preg_quote($region_token, '/') . '\b/u', '', $ql) ?? $ql;
  }
  // Remove city token (very rough, may not match declined forms)
  if ($city_token) {
    $ql = str_replace($city_token, '', $ql);
  }

  $ql = kv_geo_semantic_norm_space($ql);
  return $ql;
}

function kv_geo_semantic_clean_price_phrase(string $s, string $fallback = ''): string {
  $s = kv_geo_semantic_norm_space(mb_strtolower($s));
  if ($s === '') return $fallback;

  // normalize m2/m²
  $s = str_replace(['м2', 'м 2', 'm2', 'm 2'], 'м²', $s);

  // remove common noise words
  $noise = [
    'цена', 'цены', 'стоимость', 'сколько', 'стоит', 'купить', 'заказать', 'за м²', 'за м2',
    'в наличии', 'дешево', 'недорого', 'онлайн',
  ];
  // Keep "за м²" if it appears with material/service
  $keep_per_m2 = (bool) preg_match('/за\s*м²/u', $s);
  foreach ($noise as $w) {
    if ($w === 'за м²' || $w === 'за м2') continue;
    $s = preg_replace('/\b' . preg_quote($w, '/') . '\b/u', '', $s) ?? $s;
  }

  $s = kv_geo_semantic_norm_space($s);
  if ($s === '') $s = $fallback;

  if ($keep_per_m2 && $s) {
    // Avoid double "за м²" if already present
    if (!preg_match('/за\s*м²/u', $s)) $s .= ' за м²';
  }

  // Normalize common genitive forms to nominative (light dictionary)
  $map = [
    'металлочерепицы' => 'металлочерепица',
    'профнастила' => 'профнастил',
    'сайдинга' => 'сайдинг',
    'евроштакетника' => 'евроштакетник',
    'мягкой кровли' => 'мягкая кровля',
    'фальцевой кровли' => 'фальцевая кровля',
    'цокольного сайдинга' => 'цокольный сайдинг',
  ];
  $s = $map[$s] ?? $s;

  return kv_geo_semantic_norm_space($s);
}

function kv_geo_semantic_questionify(string $query, string $intent_slug, array $vars, string $region_slug = '', string $stage = ''): string {
  $intent_slug = sanitize_title($intent_slug);
  $city = (string)($vars['city'] ?? ($vars['{city}'] ?? ''));
  $city_prep = (string)($vars['city_prep'] ?? ($vars['{city_prep}'] ?? ''));
  $city_in = (string)($vars['city_in'] ?? ($vars['{city_in}'] ?? ''));
  if (!$city_in) {
    $city_in = function_exists('kv_geo_ru_city_in') ? kv_geo_ru_city_in($city ?: $city_prep) : ('в ' . ($city_prep ?: $city));
  }
  $year = (string)($vars['year'] ?? ($vars['{year}'] ?? date('Y')));
  $service_name = (string)($vars['service_name'] ?? ($vars['{service_name}'] ?? ''));
  $service_gen = (string)($vars['service_name_genitive'] ?? $service_name);

  $q = kv_geo_semantic_norm_space($query);
  if (kv_geo_semantic_is_question($q)) {
    return kv_geo_semantic_ensure_qmark($q);
  }

  $clean = kv_geo_semantic_strip_geo_tokens($q, $region_slug, $city);
  if ($clean === '') $clean = mb_strtolower($q);

  // intent-specific framing
  switch ($intent_slug) {
    case 'tsena':
      $subject = kv_geo_semantic_clean_price_phrase($clean, $service_gen);
      return kv_geo_semantic_ensure_qmark("Сколько стоит {$subject} {$city_in} в {$year} году");
    case 'materialy':
      return kv_geo_semantic_ensure_qmark("Как выбрать {$clean} для {$service_gen} {$city_in}");
    case 'remont':
      return kv_geo_semantic_ensure_qmark("Что делать, если {$clean} {$city_in}");
    case 'montazh':
      return kv_geo_semantic_ensure_qmark("Как проходит {$clean} {$city_in}");
    case 'pod-klyuch':
      return kv_geo_semantic_ensure_qmark("Что входит в {$clean} под ключ {$city_in}");
    case 'kalkulyator':
      return kv_geo_semantic_ensure_qmark("Как рассчитать {$clean} онлайн");
    default:
      // If intent is empty, use stage hint (price/materials/repair/installation)
      $stage = sanitize_title($stage);
      if (!$intent_slug && $stage) {
        if ($stage === 'price') {
          $subject = kv_geo_semantic_clean_price_phrase($clean, $service_gen);
          return kv_geo_semantic_ensure_qmark("Сколько стоит {$subject} {$city_in}");
        }
        if ($stage === 'materials') {
          return kv_geo_semantic_ensure_qmark("Какие материалы выбрать для {$service_gen} {$city_in}");
        }
        if ($stage === 'repair' || $stage === 'problem') {
          return kv_geo_semantic_ensure_qmark("Что делать, если {$clean} {$city_in}");
        }
        if ($stage === 'installation') {
          return kv_geo_semantic_ensure_qmark("Как проходит {$service_name} {$city_in}");
        }
      }
      return kv_geo_semantic_ensure_qmark("Как решить задачу «{$clean}» по теме {$service_name} {$city_in}");
  }
}

function kv_geo_semantic_answerify(string $query, string $intent_slug, array $vars): string {
  $intent_slug = sanitize_title($intent_slug);
  $city = (string)($vars['city'] ?? ($vars['{city}'] ?? ''));
  $city_prep = (string)($vars['city_prep'] ?? ($vars['{city_prep}'] ?? ''));
  $city_in = (string)($vars['city_in'] ?? ($vars['{city_in}'] ?? ''));
  if (!$city_in) {
    $base = $city ?: $city_prep;
    $city_in = function_exists('kv_geo_ru_city_in') ? kv_geo_ru_city_in($base) : ('в ' . ($city_prep ?: $city));
  }
  $service_name = (string)($vars['service_name'] ?? ($vars['{service_name}'] ?? ''));
  $service_gen = (string)($vars['service_name_genitive'] ?? $service_name);
  $price_from = (string)($vars['price_from'] ?? ($vars['{price_from}'] ?? ''));
  $price_unit = (string)($vars['price_unit'] ?? ($vars['{price_unit}'] ?? ''));

  $base_cta = "Точный расчёт делаем после замера и фиксации условий в смете.";

  switch ($intent_slug) {
    case 'tsena':
      if ($price_from && $price_unit) {
        return "Ориентир по работам {$city_in}: от {$price_from} {$price_unit}. Итог зависит от материала, объёма и сложности узлов. {$base_cta}";
      }
      return "Цена зависит от материала, объёма и сложности узлов. {$base_cta}";

    case 'materialy':
      return "Выбор материалов зависит от бюджета, срока службы и требований к внешнему виду/шуму/теплу. Мы сравниваем 2–3 варианта и считаем смету в двух бюджетах. {$base_cta}";

    case 'remont':
      return "Начните с диагностики причины (протечки, повреждения, ошибки узлов). Обычно ремонт включает устранение причины, замену повреждённых участков и восстановление изоляции. {$base_cta}";

    case 'montazh':
      return "Работаем по схеме: замер → смета → доставка → монтаж → приёмка → гарантия. Сроки зависят от объёма, чаще всего 3–10 дней. {$base_cta}";

    case 'pod-klyuch':
      return "{$service_name} под ключ — это материалы, доставка, монтаж одной бригадой, один договор и гарантия. Комплектацию и цену фиксируем в смете после замера. {$base_cta}";

    case 'kalkulyator':
      return "Для онлайн‑расчёта выберите тип работ и материал, укажите площадь/длину — получите ориентировочную сумму. Точная смета требует замера: влияют узлы, демонтаж и подготовка. {$base_cta}";

    default:
      return "Дадим короткий план и расчёт под ваш объект: подберём материалы, обозначим сроки и зафиксируем цену в смете. {$base_cta}";
  }
}

/**
 * Build PAA items from semantic core for the given context.
 * Returns: array of [q, a, shows, source_query]
 */
function kv_geo_semantic_paa_items(string $region_slug, string $service_slug, string $intent_slug, array $vars, int $limit = 6): array {
  $region_slug = sanitize_title($region_slug);
  $service_slug = sanitize_title($service_slug);
  $intent_slug = sanitize_title($intent_slug);
  if (!$region_slug || !$service_slug || !$intent_slug) return [];

  $entry = kv_geo_semantic_pick_entry($region_slug, $service_slug, $intent_slug);
  if (!$entry) return [];

  $queries = kv_geo_semantic_top_queries($entry, $region_slug, max(8, $limit * 2));
  if (!$queries) return [];

  $out = [];
  $seen = [];

  foreach ($queries as $row) {
    $q0 = (string)($row['query'] ?? '');
    if (!$q0) continue;

    $q = kv_geo_semantic_questionify($q0, $intent_slug, $vars, $region_slug);
    $a = kv_geo_semantic_answerify($q0, $intent_slug, $vars);

    $key = mb_strtolower(trim($q));
    if (isset($seen[$key])) continue;
    $seen[$key] = true;

    $out[] = [
      'q' => $q,
      'a' => $a,
      'shows' => (int)($row['shows'] ?? 0),
      'source_query' => $q0,
    ];

    if (count($out) >= $limit) break;
  }

  return $out;
}

/**
 * PAA for /geo/{city}/{service}/ (no intent): 3-4 компактных вопроса по услуге.
 */
function kv_geo_semantic_paa_items_service(string $region_slug, string $service_slug, array $vars, int $limit = 4): array {
  $region_slug = sanitize_title($region_slug);
  $service_slug = sanitize_title($service_slug);
  if (!$region_slug || !$service_slug) return [];

  $entry = kv_geo_semantic_pick_entry($region_slug, $service_slug, '');
  if (!$entry) return [];
  $stage = (string)($entry['stage'] ?? '');

  $queries = kv_geo_semantic_top_queries($entry, $region_slug, max(10, $limit * 3));
  if (!$queries) return [];

  $out = [];
  $seen = [];

  foreach ($queries as $row) {
    $q0 = (string)($row['query'] ?? '');
    if (!$q0) continue;

    $q = kv_geo_semantic_questionify($q0, '', $vars, $region_slug, $stage);
    $a = kv_geo_semantic_answerify($q0, '', $vars);

    $key = mb_strtolower(trim($q));
    if (isset($seen[$key])) continue;
    $seen[$key] = true;

    $out[] = [
      'q' => $q,
      'a' => $a,
      'shows' => (int)($row['shows'] ?? 0),
      'source_query' => $q0,
    ];

    if (count($out) >= $limit) break;
  }

  return $out;
}

