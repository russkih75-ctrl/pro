<?php
if (!defined('ABSPATH')) exit;

/**
 * Sitemaps & robots controls:
 * - custom GEO sitemap index + city/service/intent sub-sitemaps
 * - exclude non-indexable GEO pages
 * - add Clean-param to robots.txt (Yandex directive)
 * - link GEO sitemap in robots.txt
 */

// ── Robots.txt enhancements ──
add_filter('robots_txt', function ($output, $public) {
  $output .= "\n# KV GEO\n";
  $output .= "Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term&utm_id&utm_source_platform&utm_creative_format&utm_marketing_tactic /\n";
  $output .= "Clean-param: fbclid&gclid&yclid&openstat&from&ref /\n";
  $output .= "Disallow: /?s=\n";
  $output .= "Disallow: /search/\n";
  $output .= "\n# Sitemaps\n";
  $output .= "Sitemap: " . home_url('/kv-geo-sitemap.xml') . "\n";
  $output .= "Sitemap: " . home_url('/wp-sitemap.xml') . "\n";

  // Yandex Host directive (helps when multiple mirrors exist)
  $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
  if ($host) {
    $output .= "Host: " . $host . "\n";
  }

  // AI bot directives (allow crawling, link to llms.txt)
  $site = home_url('/');
  $output .= "\n# AI Bots — welcome\n";
  $output .= "User-agent: GPTBot\n";
  $output .= "Allow: /\n";
  $output .= "Disallow: /wp-admin/\n\n";
  $output .= "User-agent: ClaudeBot\n";
  $output .= "Allow: /\n";
  $output .= "Disallow: /wp-admin/\n\n";
  $output .= "User-agent: PerplexityBot\n";
  $output .= "Allow: /\n";
  $output .= "Disallow: /wp-admin/\n\n";
  $output .= "User-agent: Applebot-Extended\n";
  $output .= "Allow: /\n";
  $output .= "Disallow: /wp-admin/\n\n";
  $output .= "# LLM content index\n";
  $output .= "# llms.txt: " . $site . "llms.txt\n";
  $output .= "# llms-full.txt: " . $site . "llms-full.txt\n";

  return $output;
}, 20, 2);

// ── Rewrite rules ──
add_action('init', function () {
  add_rewrite_rule('^kv-geo-sitemap\.xml$', 'index.php?kv_geo_sitemap=index', 'top');
  add_rewrite_rule('^kv-geo-sitemap-cities\.xml$', 'index.php?kv_geo_sitemap=cities', 'top');
  add_rewrite_rule('^kv-geo-sitemap-services\.xml$', 'index.php?kv_geo_sitemap=services', 'top');
}, 21);

// Ensure sitemap rewrite rules are flushed after rule changes.
add_action('admin_init', function () {
  $rewrite_version = 2;
  if ((int) get_option('kv_geo_sitemaps_rewrite_version', 0) >= $rewrite_version) return;
  if (!current_user_can('manage_options')) return;
  flush_rewrite_rules(false);
  update_option('kv_geo_sitemaps_rewrite_version', $rewrite_version, false);
});

// Fallback: flush on first admin visit when deploy skipped admin_init.
// Moved from frontend init to admin_init to avoid performance hit on every page load.
add_action('admin_init', function () {
  $rewrite_version = 2;
  if ((int) get_option('kv_geo_sitemaps_rewrite_version', 0) >= $rewrite_version) return;
  flush_rewrite_rules(false);
  update_option('kv_geo_sitemaps_rewrite_version', $rewrite_version, false);
}, 99);

add_filter('query_vars', function ($vars) {
  $vars[] = 'kv_geo_sitemap';
  return $vars;
});

// ── Sitemap output ──
add_action('template_redirect', function () {
  $type = get_query_var('kv_geo_sitemap');
  if (!$type) {
    $path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    if (strpos($path, '/kv-geo-sitemap.xml') === 0) $type = 'index';
    if (strpos($path, '/kv-geo-sitemap-cities.xml') === 0) $type = 'cities';
    if (strpos($path, '/kv-geo-sitemap-services.xml') === 0) $type = 'services';
  }
  if (!$type) return;

  header('Content-Type: application/xml; charset=utf-8');

  switch ($type) {
    case 'index':
      kv_geo_sitemap_index();
      break;
    case 'cities':
      kv_geo_sitemap_cities();
      break;
    case 'services':
      kv_geo_sitemap_services();
      break;
    default:
      status_header(404);
      echo '<?xml version="1.0"?><error>Unknown sitemap type</error>';
  }
  exit;
}, 0);

/**
 * Sitemap index — links to sub-sitemaps.
 */
function kv_geo_sitemap_index() {
  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

  $sitemaps = [
    home_url('/kv-geo-sitemap-cities.xml'),
    home_url('/kv-geo-sitemap-services.xml'),
  ];

  $lastmod = date('Y-m-d\TH:i:s+00:00');
  foreach ($sitemaps as $loc) {
    echo "  <sitemap>\n";
    echo "    <loc>" . esc_url($loc) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "  </sitemap>\n";
  }

  echo "</sitemapindex>\n";
}

/**
 * City hub pages: /geo/{city}/
 */
function kv_geo_sitemap_cities() {
  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

  // Hub page /geo/
  echo "  <url>\n";
  echo "    <loc>" . esc_url(home_url('/geo/')) . "</loc>\n";
  echo "    <changefreq>weekly</changefreq>\n";
  echo "    <priority>0.8</priority>\n";
  echo "  </url>\n";

  $cities = kv_geo_get_cities();
  foreach ($cities as $c) {
    if (!is_array($c)) continue;
    if (!kv_geo_city_is_indexable($c)) continue;
    $slug = sanitize_title($c['slug'] ?? '');
    if (!$slug) continue;

    $loc = esc_url(home_url('/geo/' . $slug . '/'));
    $priority = '0.7';

    // Higher priority for larger cities
    $pop = (int)($c['population'] ?? 0);
    if ($pop >= 500000) $priority = '0.9';
    elseif ($pop >= 100000) $priority = '0.8';

    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
  }

  echo "</urlset>\n";
}

/**
 * Service+intent pages for indexable cities:
 * /geo/{city}/{service}/
 * /geo/{city}/{service}/{intent}/
 */
function kv_geo_sitemap_services() {
  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

  $cities   = kv_geo_get_cities();
  $services = kv_geo_get_services();
  $intents  = kv_geo_get_intents();

  $count = 0;
  $maxUrls = 5000; // Keep under sitemap limit

  foreach ($cities as $c) {
    if (!is_array($c)) continue;
    if (!kv_geo_city_is_indexable($c)) continue;
    $citySlug = sanitize_title($c['slug'] ?? '');
    if (!$citySlug) continue;

    foreach ($services as $s) {
      if (!is_array($s)) continue;
      $svcSlug = sanitize_title($s['slug'] ?? '');
      if (!$svcSlug) continue;

      // City + Service page
      if ($count < $maxUrls) {
        $loc = esc_url(home_url("/geo/{$citySlug}/{$svcSlug}/"));
        echo "  <url>\n";
        echo "    <loc>{$loc}</loc>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
        $count++;
      }

      // City + Service + Intent pages
      foreach ($intents as $i) {
        if (!is_array($i)) continue;
        $intSlug = sanitize_title($i['slug'] ?? '');
        if (!$intSlug) continue;

        if ($count >= $maxUrls) break 3;

        $loc = esc_url(home_url("/geo/{$citySlug}/{$svcSlug}/{$intSlug}/"));
        echo "  <url>\n";
        echo "    <loc>{$loc}</loc>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.5</priority>\n";
        echo "  </url>\n";
        $count++;
      }
    }
  }

  echo "</urlset>\n";
}

/**
 * Also register in the WP native sitemap index (/wp-sitemap.xml).
 */
add_filter('wp_sitemaps_index_entry', function ($entry, $type, $subtype, $page) {
  return $entry;
}, 10, 4);

// Ping search engines after sitemap update (on post publish)
add_action('transition_post_status', function ($new, $old, $post) {
  if ($new !== 'publish' || $old === 'publish') return;
  // Debounce: only ping once per hour
  $lastPing = (int) get_transient('kv_sitemap_last_ping');
  if ($lastPing && (time() - $lastPing) < 3600) return;

  set_transient('kv_sitemap_last_ping', time(), 7200);

  // Ping Yandex
  wp_remote_get('https://webmaster.yandex.ru/ping?sitemap=' . urlencode(home_url('/kv-geo-sitemap.xml')), [
    'timeout' => 5,
    'blocking' => false,
  ]);
}, 99, 3);
