<?php
if (!defined('ABSPATH')) exit;

/**
 * GEO data source:
 * - JSON files in /data/ directory
 * - cities.json, regions.json, services.json, intents.json
 */

function kv_geo_data_path(string $rel): string {
  return rtrim(KV_GEO_DIR, '/\\') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $rel;
}

function kv_geo_load_json(string $rel): array {
  static $cache = [];
  if (isset($cache[$rel])) return $cache[$rel];

  $p = kv_geo_data_path($rel);
  if (!file_exists($p)) return [];
  $raw = file_get_contents($p);
  if ($raw === false) return [];
  $data = json_decode($raw, true);
  $result = is_array($data) ? $data : [];
  $cache[$rel] = $result;
  return $result;
}

/* === Partners (payment/showroom points) === */
function kv_geo_get_partners(): array {
  return kv_geo_load_json('partners.json');
}

function kv_geo_find_partner(string $id): ?array {
  $id = trim((string)$id);
  if ($id === '') return null;
  foreach (kv_geo_get_partners() as $p) {
    if (!is_array($p)) continue;
    if ((string)($p['id'] ?? '') === $id) return $p;
  }
  return null;
}

function kv_geo_get_main_partner(): ?array {
  $partners = kv_geo_get_partners();
  foreach ($partners as $p) {
    if (!is_array($p)) continue;
    if (!empty($p['is_main'])) return $p;
  }
  foreach ($partners as $p) {
    if (!is_array($p)) continue;
    if (!empty($p['id'])) return $p;
  }
  return null;
}

function kv_geo_get_partner_points(): array {
  return kv_geo_load_json('partner-points.json');
}

function kv_geo_get_partner_points_for_city(string $city_slug): array {
  $city_slug = sanitize_title($city_slug);
  if ($city_slug === '') return [];

  $out = [];
  $seen = [];
  foreach (kv_geo_get_partner_points() as $pt) {
    if (!is_array($pt)) continue;
    if (sanitize_title((string)($pt['city_slug'] ?? '')) !== $city_slug) continue;

    $partner_id = trim((string)($pt['partner_id'] ?? ''));
    $url = trim((string)($pt['url'] ?? ''));
    $route_url = trim((string)($pt['route_url'] ?? ''));
    if ($route_url === '') $route_url = $url;
    $address = trim((string)($pt['address'] ?? ''));
    $area_hint = trim((string)($pt['area_hint'] ?? ''));
    if ($partner_id === '' || $route_url === '') continue;

    $id = trim((string)($pt['id'] ?? ''));
    if ($id !== '') {
      $k = function_exists('mb_strtolower') ? mb_strtolower($id) : strtolower($id);
      if (isset($seen[$k])) continue;
      $seen[$k] = true;
    }

    $partner = kv_geo_find_partner($partner_id);
    $pt['partner'] = $partner;
    $pt['partner_name'] = (string)($partner['name'] ?? '');
    $pt['link_rel'] = (string)($partner['link_rel_default'] ?? 'nofollow noopener noreferrer');
    $pt['route_url'] = $route_url;
    $pt['address'] = $address;
    $pt['area_hint'] = $area_hint;

    $out[] = $pt;
  }

  return $out;
}

/* === Regions === */
function kv_geo_get_regions(): array {
  return kv_geo_load_json('regions.json');
}

function kv_geo_find_region(string $slug): ?array {
  $slug = sanitize_title($slug);
  foreach (kv_geo_get_regions() as $r) {
    if (!is_array($r)) continue;
    if (($r['slug'] ?? '') === $slug) return $r;
  }
  return null;
}

/* === Cities === */
function kv_geo_get_cities(): array {
  return kv_geo_load_json('cities.json');
}

function kv_geo_find_city(string $slug): ?array {
  $slug = sanitize_title($slug);
  foreach (kv_geo_get_cities() as $c) {
    if (!is_array($c)) continue;
    if (($c['slug'] ?? '') === $slug) return $c;
  }
  return null;
}

function kv_geo_get_cities_by_region(string $region_slug): array {
  $region_slug = sanitize_title($region_slug);
  $result = [];
  foreach (kv_geo_get_cities() as $c) {
    if (!is_array($c)) continue;
    if (($c['region'] ?? '') === $region_slug) $result[] = $c;
  }
  return $result;
}

/* === Services === */
function kv_geo_get_services(): array {
  return kv_geo_load_json('services.json');
}

function kv_geo_find_service(string $slug): ?array {
  $slug = sanitize_title($slug);
  foreach (kv_geo_get_services() as $s) {
    if (!is_array($s)) continue;
    if (($s['slug'] ?? '') === $slug) return $s;
  }
  return null;
}

/* === Intents === */
function kv_geo_get_intents(): array {
  return kv_geo_load_json('intents.json');
}

function kv_geo_find_intent(string $slug): ?array {
  $slug = sanitize_title($slug);
  foreach (kv_geo_get_intents() as $i) {
    if (!is_array($i)) continue;
    if (($i['slug'] ?? '') === $slug) return $i;
  }
  return null;
}

/* === Template helpers === */

/* === RU morphology (lightweight) === */
function kv_geo_ru_city_prep(string $name): string {
  $name = trim((string)$name);
  if ($name === '') return $name;

  // Explicit overrides for cities with irregular declension
  static $overrides = [
    'фролово'   => 'Фролово',
    'иваново'   => 'Иваново',
    'кемерово'  => 'Кемерово',
    'одинцово'  => 'Одинцово',
  ];

  $lower = mb_strtolower($name);

  if (isset($overrides[$lower])) return $overrides[$lower];

  // indeclinable common cases
  $indecl = ['сочи'];
  if (in_array($lower, $indecl, true)) return $name;

  // Hyphenated: склоняем последнюю часть
  if (mb_strpos($name, '-') !== false) {
    $parts = explode('-', $name);
    $last = array_pop($parts);
    $lastPrep = kv_geo_ru_city_prep($last);
    $parts[] = $lastPrep;
    return implode('-', $parts);
  }

  // Multiword: склоняем последнее слово
  if (preg_match('/\s/u', $name)) {
    $parts = preg_split('/\s+/u', $name) ?: [$name];
    $last = array_pop($parts);
    $lastPrep = kv_geo_ru_city_prep($last);
    $parts[] = $lastPrep;
    return implode(' ', $parts);
  }

  $n = $name;
  $last2 = mb_substr($n, -2);
  $last3 = mb_substr($n, -3);
  $last1 = mb_substr($n, -1);

  // -ск -> -ске (Борисоглебск -> Борисоглебске)
  if ($last2 === 'ск') return $n . 'е';

  // -ово/-ево/-ино -> -ове/-еве/-ине (очень грубо, но лучше чем номинатив)
  if ($last3 === 'ово') return mb_substr($n, 0, -3) . 'ове';
  if ($last3 === 'ево') return mb_substr($n, 0, -3) . 'еве';
  if ($last3 === 'ино') return mb_substr($n, 0, -3) . 'ине';

  // -а/-я -> -е (Анна -> Анне, Бутурлиновка -> Бутурлиновке)
  if ($last1 === 'а') return mb_substr($n, 0, -1) . 'е';
  if ($last1 === 'я') return mb_substr($n, 0, -1) . 'е';

  // -ь -> -и (Тверь -> Твери) — для городов часто так
  if ($last1 === 'ь') return mb_substr($n, 0, -1) . 'и';

  // -й -> -е (Край -> Крае)
  if ($last1 === 'й') return mb_substr($n, 0, -1) . 'е';

  // default: add -е
  return $n . 'е';
}

function kv_geo_ru_city_in(string $name): string {
  $prep = kv_geo_ru_city_prep($name);
  $first = mb_substr($prep, 0, 1);
  $second = mb_substr($prep, 1, 1);
  $vowels = ['а','е','ё','и','о','у','ы','э','ю','я','А','Е','Ё','И','О','У','Ы','Э','Ю','Я'];
  $needs_vo = ($first === 'В' || $first === 'в') && $second !== '' && !in_array($second, $vowels, true);
  return ($needs_vo ? 'во ' : 'в ') . $prep;
}

/**
 * Build page title from context
 */
function kv_geo_build_title(array $ctx): string {
  $city = $ctx['city'] ?? null;
  $city_name = $city['name'] ?? 'Город';
  $city_prep = kv_geo_ru_city_prep((string)$city_name);
  $service_data = $ctx['service'] ? kv_geo_find_service($ctx['service']) : null;
  $intent_data  = $ctx['intent'] ? kv_geo_find_intent($ctx['intent']) : null;

  if ($service_data && $intent_data) {
    $price_from = '';
    if ($city && isset($city['services'][$ctx['service']]['price_from'])) {
      $price_from = $city['services'][$ctx['service']]['price_from'];
    }
    $tpl = $service_data['title_template'] ?? '{service} в {city}';
    $tpl = str_replace('{service}', $service_data['name'] ?? '', $tpl);
    $tpl = str_replace('{city}', $city_name, $tpl);
    $tpl = str_replace('{city_prep}', $city_prep, $tpl);
    $tpl = str_replace('{price_from}', $price_from, $tpl);
    return $tpl . ' — ' . ($intent_data['title_suffix'] ?? '');
  }

  if ($service_data) {
    $price_from = '';
    if ($city && isset($city['services'][$ctx['service']]['price_from'])) {
      $price_from = $city['services'][$ctx['service']]['price_from'];
    }
    $tpl = $service_data['title_template'] ?? '{service} в {city}';
    $tpl = str_replace('{service}', $service_data['name'] ?? '', $tpl);
    $tpl = str_replace('{city}', $city_name, $tpl);
    $tpl = str_replace('{city_prep}', $city_prep, $tpl);
    $tpl = str_replace('{price_from}', $price_from, $tpl);
    return $tpl;
  }

  return "Кровля, фасады, заборы в {$city_prep} — материалы и монтаж под ключ";
}

/**
 * Build H1 heading from context
 */
function kv_geo_build_h1(array $ctx): string {
  $city = $ctx['city'] ?? null;
  $city_name = $city['name'] ?? 'Город';
  $city_prep = kv_geo_ru_city_prep((string)$city_name);
  $service_data = $ctx['service'] ? kv_geo_find_service($ctx['service']) : null;
  $intent_data  = $ctx['intent'] ? kv_geo_find_intent($ctx['intent']) : null;

  if ($service_data && $intent_data) {
    $h1 = $service_data['h1_template'] ?? '{service} в {city}';
    $h1 = str_replace('{service}', $service_data['name'] ?? '', $h1);
    $h1 = str_replace('{city}', $city_name, $h1);
    $h1 = str_replace('{city_prep}', $city_prep, $h1);
    $suffix = $intent_data['h1_suffix'] ?? '';
    $suffix = str_replace('{year}', date('Y'), $suffix);
    return $h1 . ' ' . $suffix;
  }

  if ($service_data) {
    $h1 = $service_data['h1_template'] ?? '{service} в {city}';
    $h1 = str_replace('{service}', $service_data['name'] ?? '', $h1);
    $h1 = str_replace('{city}', $city_name, $h1);
    $h1 = str_replace('{city_prep}', $city_prep, $h1);
    return $h1;
  }

  return "Кровля, фасады и заборы в {$city_prep}";
}

/**
 * Build meta description from context
 */
function kv_geo_build_description(array $ctx): string {
  $city = $ctx['city'] ?? null;
  $city_name = $city['name'] ?? 'Город';
  $city_prep = kv_geo_ru_city_prep((string)$city_name);
  $service_data = $ctx['service'] ? kv_geo_find_service($ctx['service']) : null;
  $intent_data  = $ctx['intent'] ? kv_geo_find_intent($ctx['intent']) : null;

  if ($service_data) {
    $tpl = $service_data['description_template'] ?? '';
    $price_from = '';
    if ($city && isset($city['services'][$ctx['service']]['price_from'])) {
      $price_from = $city['services'][$ctx['service']]['price_from'];
    }
    $tpl = str_replace('{service}', $service_data['name'] ?? '', $tpl);
    $tpl = str_replace('{city}', $city_name, $tpl);
    $tpl = str_replace('{city_prep}', $city_prep, $tpl);
    $tpl = str_replace('{price_from}', $price_from, $tpl);
    
    if ($intent_data && !empty($intent_data['description'])) {
      $tpl .= ' ' . $intent_data['description'];
    }
    
    return $tpl;
  }

  $desc = "Кровля, фасады, заборы в {$city_prep}. Материалы и монтаж под ключ. Замер бесплатно. Гарантия 10 лет.";
  if ($intent_data && !empty($intent_data['description'])) {
    $desc .= ' ' . $intent_data['description'];
  }
  return $desc;
}
