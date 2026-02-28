<?php
if (!defined('ABSPATH')) exit;

/**
 * Dzen RSS feed (early MU-plugin registration).
 * Some hosting stacks resolve /feed/* before theme hooks are attached.
 */
function kv_mu_dzen_feed_render() {
  $posts = get_posts([
    'post_type' => ['post', 'guide'],
    'post_status' => 'publish',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
  ]);

  header('Content-Type: application/rss+xml; charset=utf-8');
  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  ?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo esc_html(get_bloginfo('name')); ?></title>
<link><?php echo esc_url(home_url('/')); ?></link>
<description><?php echo esc_html(get_bloginfo('description')); ?></description>
<language>ru</language>
<atom:link href="<?php echo esc_url(home_url('/feed/dzen/')); ?>" rel="self" type="application/rss+xml" />
<?php foreach ($posts as $p) :
  $content = apply_filters('the_content', (string) $p->post_content);
  $content = str_replace(']]>', ']]&gt;', $content);
  $excerpt = has_excerpt($p) ? get_the_excerpt($p) : wp_trim_words(wp_strip_all_tags($content), 40, '...');
  $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
?>
<item>
<title><?php echo esc_html(get_the_title($p)); ?></title>
<link><?php echo esc_url(get_permalink($p)); ?></link>
<guid isPermaLink="true"><?php echo esc_url(get_permalink($p)); ?></guid>
<pubDate><?php echo esc_html(get_post_time('D, d M Y H:i:s +0000', true, $p)); ?></pubDate>
<dc:creator><?php echo esc_html((string) get_the_author_meta('display_name', (int) $p->post_author)); ?></dc:creator>
<description><![CDATA[<?php echo $excerpt; ?>]]></description>
<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
</item>
<?php endforeach; ?>
</channel>
</rss>
<?php
  exit;
}

add_action('init', function () {
  add_feed('dzen', 'kv_mu_dzen_feed_render');
  add_action('do_feed_dzen', 'kv_mu_dzen_feed_render', 10, 2);
}, 1);

add_filter('redirect_canonical', function ($redirect_url) {
  $pathOnly = (string) (parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
  if (strpos($pathOnly, '/feed/dzen') === 0) return false;
  if (strpos($pathOnly, '/feed-2') === 0) return false;
  return $redirect_url;
}, 10, 1);

/**
 * Legacy feed URLs: some bots probe /feed-2/.
 * Redirect to canonical feed endpoints to avoid 404 in webmaster tools.
 */
add_action('template_redirect', function () {
  if (is_admin()) return;
  $pathOnly = (string) (parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
  if ($pathOnly === '') return;

  if ($pathOnly === '/feed-2/' || $pathOnly === '/feed-2') {
    wp_redirect(home_url('/feed/'), 301);
    exit;
  }
  if ($pathOnly === '/feed-2/dzen/' || $pathOnly === '/feed-2/dzen') {
    wp_redirect(home_url('/feed/dzen/'), 301);
    exit;
  }
}, 0);

/**
 * Virtual GEO pages (no DB posts):
 * - /geo/{city}/
 * - /geo/{city}/{service}/
 * - /geo/{city}/{service}/{intent}/
 */

function kv_geo_query_var(): string { return 'kv_geo'; }
function kv_geo_hub_query_var(): string { return 'kv_geo_hub'; }

function kv_geo_register_query_vars($vars) {
  $vars[] = kv_geo_query_var();
  $vars[] = kv_geo_hub_query_var();
  $vars[] = 'kv_city';
  $vars[] = 'kv_service';
  $vars[] = 'kv_intent';
  return $vars;
}
add_filter('query_vars', 'kv_geo_register_query_vars');

function kv_geo_add_rewrite_rules() {
  // /geo/ hub page
  add_rewrite_rule('^geo/?$', 'index.php?' . kv_geo_query_var() . '=1&' . kv_geo_hub_query_var() . '=1', 'top');
  add_rewrite_rule('^geo/([^/]+)/?$', 'index.php?' . kv_geo_query_var() . '=1&kv_city=$matches[1]', 'top');
  add_rewrite_rule('^geo/([^/]+)/([^/]+)/?$', 'index.php?' . kv_geo_query_var() . '=1&kv_city=$matches[1]&kv_service=$matches[2]', 'top');
  add_rewrite_rule('^geo/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?' . kv_geo_query_var() . '=1&kv_city=$matches[1]&kv_service=$matches[2]&kv_intent=$matches[3]', 'top');
}
add_action('init', 'kv_geo_add_rewrite_rules', 20);

/**
 * MU plugins don't have activation hooks; flush rewrites once in admin.
 */
add_action('admin_init', function () {
  if (get_option('kv_geo_rewrite_flushed', '') === '1') return;
  if (!current_user_can('manage_options')) return;
  flush_rewrite_rules(false);
  update_option('kv_geo_rewrite_flushed', '1', false);
});

function kv_geo_is_request(): bool {
  return (bool) get_query_var(kv_geo_query_var());
}

function kv_geo_get_context(): array {
  static $cached = null;
  if ($cached !== null) return $cached;

  $is_hub = (bool) get_query_var(kv_geo_hub_query_var());
  $city_slug = (string) get_query_var('kv_city');
  $service   = (string) get_query_var('kv_service');
  $intent    = (string) get_query_var('kv_intent');

  $city = (!$is_hub && $city_slug) ? kv_geo_find_city($city_slug) : null;
  $region = $city ? kv_geo_find_region((string)($city['region'] ?? '')) : null;

  $cached = [
    'hub'       => $is_hub,
    'city_slug' => sanitize_title($city_slug),
    'service'   => sanitize_title($service),
    'intent'    => sanitize_title($intent),
    'city'      => $city,
    'region'    => $region,
    'indexable'  => $is_hub ? true : kv_geo_city_is_indexable($city),
    'unique_score' => $is_hub ? 100 : kv_geo_score_city($city),
    'validation_errors' => $is_hub ? [] : kv_geo_city_validation_errors($city),
  ];
  return $cached;
}

/* Force 200 for virtual pages */
add_action('template_redirect', function () {
  if (!kv_geo_is_request()) return;
  global $wp_query;

  $ctx = kv_geo_get_context();
  if (!empty($ctx['hub'])) {
    $wp_query->is_404 = false;
    status_header(200);
    return;
  }
  // 404 if city not found
  if (!$ctx['city']) {
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    return;
  }

  // 404 if service/intent slugs are unknown (avoid soft-404 on invalid GEO URLs)
  if (!empty($ctx['service']) && !kv_geo_find_service((string)$ctx['service'])) {
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    return;
  }
  if (!empty($ctx['intent']) && !kv_geo_find_intent((string)$ctx['intent'])) {
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    return;
  }

  $wp_query->is_404 = false;
  status_header(200);
}, 0);

/* IndexNow for indexable geo pages */
add_action('template_redirect', function () {
  if (!kv_geo_is_request()) return;
  if (!function_exists('kv_indexnow_enqueue_url')) return;

  $ctx = kv_geo_get_context();
  if (!$ctx['indexable']) return;

  // Enqueue canonical URL (strip tracking params, unify trailing slash)
  if (function_exists('kv_geo_canonical_url')) {
    kv_indexnow_enqueue_url(kv_geo_canonical_url($ctx));
  } else {
    kv_indexnow_enqueue_url(home_url(add_query_arg([])));
  }
}, 5);

/**
 * Canonical URL builder for virtual GEO pages.
 * Ensures stable URL without query parameters.
 */
function kv_geo_canonical_url(array $ctx): string {
  if (!empty($ctx['hub'])) {
    return home_url('/geo/');
  }

  $city_slug = sanitize_title((string)($ctx['city_slug'] ?? ''));
  if (!$city_slug) return home_url('/geo/');

  $service = sanitize_title((string)($ctx['service'] ?? ''));
  $intent  = sanitize_title((string)($ctx['intent'] ?? ''));

  $path = '/geo/' . $city_slug . '/';
  if ($service) $path .= $service . '/';
  if ($intent)  $path .= $intent . '/';

  return home_url($path);
}

/* SEO: Document title */
add_filter('pre_get_document_title', function ($title) {
  if (!kv_geo_is_request()) return $title;
  $ctx = kv_geo_get_context();

  if (!empty($ctx['hub'])) {
    return 'Города и регионы работ — кровля, фасады, заборы | ' . get_bloginfo('name');
  }

  return kv_geo_build_title($ctx) . ' | ' . get_bloginfo('name');
}, 20);

/* SEO: Meta description */
add_action('wp_head', function () {
  if (!kv_geo_is_request()) return;
  $ctx = kv_geo_get_context();
  if (!empty($ctx['hub'])) {
    echo '<meta name="description" content="' . esc_attr('Выберите город: кровля, фасады и заборы под ключ. Замер бесплатно, гарантия до 10 лет. Смета и сроки по вашему объекту.') . '" />' . "\n";
    return;
  }
  $desc = kv_geo_build_description($ctx);
  if ($desc) {
    echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
  }
}, 2);

/* SEO: Remove WP Core canonical for GEO pages (we output our own) */
add_action('wp', function () {
  if (!kv_geo_is_request()) return;
  remove_action('wp_head', 'rel_canonical');
});

/* SEO: Canonical + og:url for virtual GEO pages */
add_action('wp_head', function () {
  if (!kv_geo_is_request()) return;
  $ctx = kv_geo_get_context();
  $canonical = kv_geo_canonical_url($ctx);
  if (!$canonical) return;

  echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
  echo '<meta property="og:url" content="' . esc_url($canonical) . '" />' . "\n";
}, 2);

/* SEO: noindex thin pages */
add_action('wp_head', function () {
  if (!kv_geo_is_request()) return;
  $ctx = kv_geo_get_context();
  if (!$ctx['indexable']) {
    // Keep crawling/weight flow through internal links even when page is noindex.
    echo '<meta name="robots" content="noindex,follow" />' . "\n";
  }
}, 1);

/* SEO: Schema.org LocalBusiness */
add_action('wp_head', function () {
  if (!kv_geo_is_request()) return;
  $ctx = kv_geo_get_context();
  if (!empty($ctx['hub'])) {
    // Minimal schema for the hub page
    $schema = [
      '@context' => 'https://schema.org',
      '@type' => 'CollectionPage',
      'name' => 'Регионы и города работ',
      'url' => home_url('/geo/'),
      'isPartOf' => [
        '@type' => 'WebSite',
        'url' => home_url('/'),
        'name' => get_bloginfo('name'),
      ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    return;
  }

  if (!$ctx['city']) return;

  $city = $ctx['city'];
  $service_data = $ctx['service'] ? kv_geo_find_service($ctx['service']) : null;
  $intent_data  = $ctx['intent'] ? kv_geo_find_intent($ctx['intent']) : null;

  $schema = [
    '@context' => 'https://schema.org',
    '@type' => 'HomeAndConstructionBusiness',
    'name' => get_bloginfo('name') . ' — ' . ($city['name'] ?? ''),
    'description' => kv_geo_build_description($ctx),
    'url' => kv_geo_canonical_url($ctx),
    'areaServed' => [
      '@type' => 'City',
      'name' => $city['name'] ?? '',
    ],
  ];

  // Commercial factors: keep contacts consistent across GEO pages (HQ contacts).
  $hq_phone = (string) get_option('kv_phone', '');
  $hq_phone_digits = preg_replace('/[^+\d]/', '', $hq_phone);
  if ($hq_phone_digits) {
    $schema['telephone'] = $hq_phone_digits;
  }
  $hq_email = (string) get_option('kv_email', '');
  if ($hq_email) {
    $schema['email'] = $hq_email;
  }
  $hq_address = (string) get_option('kv_address', '');
  if ($hq_address) {
    $schema['address'] = [
      '@type' => 'PostalAddress',
      'streetAddress' => $hq_address,
      'addressCountry' => 'RU',
    ];
  }

  if (!empty($city['lat']) && !empty($city['lng'])) {
    $schema['geo'] = [
      '@type' => 'GeoCoordinates',
      'latitude' => $city['lat'],
      'longitude' => $city['lng'],
    ];
  }

  // City-specific offices are only safe to use when they are truly owned by the business.
  if (!empty($city['offices']) && is_array($city['offices'])) {
    $office = $city['offices'][0] ?? null;
    if (is_array($office) && (($office['type'] ?? '') === 'own')) {
      $schema['address'] = [
        '@type' => 'PostalAddress',
        'streetAddress' => (string)($office['address'] ?? ''),
        'addressLocality' => (string)($city['name'] ?? ''),
        'addressCountry' => 'RU',
      ];
      if (!empty($office['phone'])) {
        $schema['telephone'] = preg_replace('/[^+\d]/', '', (string)$office['phone']);
      }
    }
  }

  // FAQ schema: combine service FAQ + intent answer-first (no duplicates)
  $faq_entities = [];
  $seen = [];

  $city_name = (string)($city['name'] ?? '');
  $price_from = '';
  $price_unit = '';
  if ($service_data && !empty($ctx['service']) && isset($city['services'][$ctx['service']])) {
    $city_svc = $city['services'][$ctx['service']];
    if (is_array($city_svc)) {
      $price_from = (string)($city_svc['price_from'] ?? '');
      $price_unit = (string)($city_svc['price_unit'] ?? '');
    }
  }
  $vars = [
    '{city}' => $city_name,
    '{city_prep}' => function_exists('kv_geo_ru_city_prep') ? kv_geo_ru_city_prep($city_name) : $city_name,
    '{year}' => date('Y'),
    '{service_name}' => (string)($service_data['name'] ?? ''),
    '{price_from}' => $price_from,
    '{price_unit}' => $price_unit,
  ];

  if ($intent_data && !empty($intent_data['qa_templates']) && is_array($intent_data['qa_templates'])) {
    foreach (array_slice($intent_data['qa_templates'], 0, 4) as $qa) {
      if (!is_array($qa)) continue;
      $q = trim(str_replace(array_keys($vars), array_values($vars), (string)($qa['h2'] ?? '')));
      $a = trim(str_replace(array_keys($vars), array_values($vars), (string)($qa['answer_first'] ?? '')));
      if (!$q || !$a) continue;
      $key = mb_strtolower($q);
      if (isset($seen[$key])) continue;
      $seen[$key] = true;
      $faq_entities[] = [
        '@type' => 'Question',
        'name' => $q,
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text' => $a,
        ],
      ];
    }
  }

  if ($service_data && !empty($service_data['faq']) && is_array($service_data['faq'])) {
    foreach (array_slice($service_data['faq'], 0, 8) as $faq) {
      if (!is_array($faq)) continue;
      $q = trim((string)($faq['q'] ?? ''));
      $a = trim((string)($faq['a'] ?? ''));
      if (!$q || !$a) continue;
      $key = mb_strtolower($q);
      if (isset($seen[$key])) continue;
      $seen[$key] = true;
      $faq_entities[] = [
        '@type' => 'Question',
        'name' => $q,
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text' => $a,
        ],
      ];
    }
  }

  // PAA from semantic core (only for intent pages), appended as extra Q/A
  if ($intent_data && $service_data && function_exists('kv_geo_semantic_paa_items')) {
    $region = $ctx['region'] ?? null;
    $region_slug = '';
    if (is_array($region) && !empty($region['slug'])) {
      $region_slug = (string)$region['slug'];
    } elseif (!empty($city['region'])) {
      $region_slug = (string)$city['region'];
    }

    if ($region_slug) {
      // Prepare vars for generator
      $vars2 = [
        'city' => $city_name,
        'year' => date('Y'),
        'service_name' => (string)($service_data['name'] ?? ''),
        'service_name_genitive' => (string)($service_data['name_genitive'] ?? ($service_data['name'] ?? '')),
        'service_name_accusative' => (string)($service_data['name_accusative'] ?? ($service_data['name'] ?? '')),
        'price_from' => $price_from,
        'price_unit' => $price_unit,
      ];

      // Add PAA only if we have room (avoid huge FAQPage)
      $room = 12 - count($faq_entities);
      if ($room > 0) {
        $paa = kv_geo_semantic_paa_items($region_slug, (string)($ctx['service'] ?? ''), (string)($ctx['intent'] ?? ''), $vars2, min(5, $room));
        if (is_array($paa) && $paa) {
          foreach ($paa as $it) {
            if (!is_array($it)) continue;
            $q = trim((string)($it['q'] ?? ''));
            $a = trim((string)($it['a'] ?? ''));
            if (!$q || !$a) continue;
            $key = mb_strtolower($q);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $faq_entities[] = [
              '@type' => 'Question',
              'name' => $q,
              'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $a,
              ],
            ];
          }
        }
      }
    }
  }

  if (!empty($faq_entities)) {
    // Safety cap (search engines may ignore too large FAQPage blocks)
    if (count($faq_entities) > 12) $faq_entities = array_slice($faq_entities, 0, 12);
    echo '<script type="application/ld+json">' . wp_json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => $faq_entities,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
  }

  // HowTo schema if service has process steps
  if ($service_data && !empty($service_data['process_steps']) && is_array($service_data['process_steps'])) {
    $steps = [];
    foreach (array_slice($service_data['process_steps'], 0, 8) as $idx => $st) {
      if (!is_array($st)) continue;
      $steps[] = [
        '@type' => 'HowToStep',
        'position' => $idx + 1,
        'name' => (string)($st['title'] ?? ''),
        'text' => (string)($st['text'] ?? ''),
      ];
    }
    if (!empty($steps)) {
      echo '<script type="application/ld+json">' . wp_json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => ($service_data['name'] ?? 'Услуга') . ' — как мы работаем',
        'step' => $steps,
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
  }

  echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";

  // Breadcrumb schema
  $breadcrumbs = [
    ['name' => 'Главная', 'url' => home_url('/')],
    ['name' => 'Регионы', 'url' => home_url('/geo/')],
  ];
  if ($ctx['city']) {
    $breadcrumbs[] = ['name' => $city['name'], 'url' => home_url('/geo/' . $ctx['city_slug'] . '/')];
  }
  if ($service_data) {
    $breadcrumbs[] = ['name' => $service_data['name'], 'url' => home_url('/geo/' . $ctx['city_slug'] . '/' . $ctx['service'] . '/')];
  }
  if ($intent_data) {
    $breadcrumbs[] = ['name' => $intent_data['name'], 'url' => ''];
  }

  $bc_items = [];
  foreach ($breadcrumbs as $i => $b) {
    $item = [
      '@type' => 'ListItem',
      'position' => $i + 1,
      'name' => $b['name'],
    ];
    if (!empty($b['url'])) $item['item'] = $b['url'];
    $bc_items[] = $item;
  }
  echo '<script type="application/ld+json">' . wp_json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $bc_items,
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 3);

/* Template: use theme's kv-geo.php */
add_filter('template_include', function ($template) {
  if (!kv_geo_is_request()) return $template;

  $ctx = kv_geo_get_context();
  if (!empty($ctx['hub'])) {
    $hub = locate_template('geo-hub.php');
    if ($hub) return $hub;
  }

  $t = locate_template('kv-geo.php');
  if ($t) return $t;

  // Fallback: use plugin's template
  $fallback = KV_GEO_DIR . '/templates/kv-geo.php';
  if (file_exists($fallback)) return $fallback;

  return $template;
}, 50);
