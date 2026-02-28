<?php
/**
 * Kvadratyra Theme Functions
 *
 * @package Kvadratyra
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

define('KV_THEME_VERSION', '2.0.0');
define('KV_THEME_DIR', get_template_directory());
define('KV_THEME_URI', get_template_directory_uri());

define('KV_DEFAULT_PHONE', '+7 (900) 304-51-90');
define('KV_DEFAULT_MAX_CHAT', 'https://max.ru/join/Eb2wTRuozpHOjt2n5uXqyzN0ouN6KLubNFZc_Zg71lU');
define('KV_DEFAULT_OK_GROUP', 'https://ok.ru/group/70000014101047');

/* ===== INCLUDES ===== */
$kv_includes = [
    'inc/schema-graph.php',
    'inc/schema-article.php',
    'inc/toc.php',
    'inc/breadcrumbs.php',
    'inc/trust-pages.php',
    'inc/lottie.php',
    'inc/cpt-guide.php',
    'inc/ai-feed.php',
    'inc/llms-txt.php',
    'inc/calc-ajax.php',
];

foreach ($kv_includes as $file) {
    $path = KV_THEME_DIR . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

/* ===== THEME SETUP ===== */
add_action('after_setup_theme', function () {
    // WordPress features
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('wp-block-styles');

    // Image sizes
    add_image_size('kv-card', 600, 400, true);
    add_image_size('kv-hero', 1200, 600, true);
    add_image_size('kv-thumb', 300, 200, true);

    // Navigation menus
    register_nav_menus([
        'primary'      => 'Главное меню',
        'footer-1'     => 'Футер — Услуги',
        'footer-2'     => 'Футер — Информация',
        'footer-3'     => 'Футер — Контакты',
    ]);
});

/* ===== ENQUEUE ASSETS ===== */
add_action('wp_enqueue_scripts', function () {
    // Google Fonts — Inter (non-blocking via media print swap trick for CWV)
    wp_enqueue_style(
        'kv-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
        [],
        null
    );

    // Vite dist files
    $dist_css = KV_THEME_DIR . '/assets/dist/style.css';
    $dist_js  = KV_THEME_DIR . '/assets/dist/main.js';

    // Bundled CSS (Vite) when it exists
    if (file_exists($dist_css)) {
        $dist_css_ver = @filemtime($dist_css) ?: KV_THEME_VERSION;
        wp_enqueue_style(
            'kv-main',
            KV_THEME_URI . '/assets/dist/style.css',
            ['kv-google-fonts'],
            $dist_css_ver
        );
    }

    // Always enqueue theme stylesheet last (WP-recognized + safe overrides if dist is stale)
    $style_deps = ['kv-google-fonts'];
    if (file_exists($dist_css)) $style_deps[] = 'kv-main';
    $style_path = get_stylesheet_directory() . '/style.css';
    $style_ver  = file_exists($style_path) ? (@filemtime($style_path) ?: KV_THEME_VERSION) : KV_THEME_VERSION;
    wp_enqueue_style(
        'kv-style',
        get_stylesheet_uri(),
        $style_deps,
        $style_ver
    );

    // Prefer bundled JS when it exists; otherwise fallback to src (dev)
    if (file_exists($dist_js)) {
        $dist_js_ver = @filemtime($dist_js) ?: KV_THEME_VERSION;
        wp_enqueue_script(
            'kv-main',
            KV_THEME_URI . '/assets/dist/main.js',
            [],
            $dist_js_ver,
            true
        );
    } else {
        $src_js = KV_THEME_DIR . '/assets/src/main.js';
        if (file_exists($src_js)) {
            $src_js_ver = @filemtime($src_js) ?: KV_THEME_VERSION;
            wp_enqueue_script(
                'kv-main',
                KV_THEME_URI . '/assets/src/main.js',
                [],
                $src_js_ver,
                true
            );
        }
    }

    // Localize script with AJAX URL and nonce
    wp_localize_script('kv-main', 'kvData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('kv_nonce'),
        'homeUrl' => home_url('/'),
        'metrikaId' => get_option('kv_yandex_metrika', '106850458'),
    ]);

    // Yandex Metrika goals (event delegation; works even if dist is stale)
    wp_add_inline_script('kv-main', <<<JS
(function(){
  if (window.__kvMetrikaBound) return;
  window.__kvMetrikaBound = true;

  function goal(name, params){
    var id = (window.kvData && window.kvData.metrikaId) ? Number(window.kvData.metrikaId) : 0;
    if (!id || typeof window.ym !== 'function') return;
    try { window.ym(id, 'reachGoal', name, params || {}); } catch(e) {}
  }

  // Link clicks: tel + telegram
  document.addEventListener('click', function(e){
    var a = e.target && e.target.closest ? e.target.closest('a') : null;
    if (!a) return;
    var href = a.getAttribute('href') || '';
    if (href.indexOf('tel:') === 0) goal('phone_click');
    if (href.indexOf('t.me/') !== -1) goal('tg_click');
    if (a.classList && a.classList.contains('kv-header-cta')) goal('cta_header_call');
  }, {capture:true, passive:true});

  // Calculator submit
  document.addEventListener('click', function(e){
    var btn = e.target && e.target.closest ? e.target.closest('[data-quiz-calc]') : null;
    if (!btn) return;
    var root = btn.closest('[data-quiz]');
    if (!root) return;
    var svc = root.querySelector('[data-quiz-service]');
    var mat = root.querySelector('[data-quiz-material]');
    var area = root.querySelector('[data-quiz-area]');
    goal('calc_submit', {
      service: svc && svc.value ? svc.value : '',
      material: mat && mat.selectedOptions && mat.selectedOptions[0] ? (mat.selectedOptions[0].textContent || '') : '',
      area: area && area.value ? area.value : ''
    });
  }, {capture:true, passive:true});

  // FAQ open (only when expanding)
  document.addEventListener('click', function(e){
    var t = e.target && e.target.closest ? e.target.closest('[data-faq-toggle]') : null;
    if (!t) return;
    var item = t.closest('.faq-item');
    if (item && item.classList && !item.classList.contains('is-open')) {
      var q = (t.textContent || '').trim().slice(0, 120);
      goal('faq_open', { q: q });
    }
  }, {capture:true, passive:true});

  // Section views (first time)
  if ('IntersectionObserver' in window) {
    var ids = ['features','services','how-it-works','faq','reviews','blog','calculator','geography'];
    var seen = {};
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(ent){
        if (!ent.isIntersecting) return;
        var id = ent.target && ent.target.id ? ent.target.id : '';
        if (!id || seen[id]) return;
        seen[id] = true;
        goal('view_' + id);
      });
    }, {threshold:0.25});
    ids.forEach(function(id){
      var el = document.getElementById(id);
      if (el) io.observe(el);
    });
  }
})();
JS, 'after');

    // CWV-safe reveal animations + ultra-light lazyload for data-src images
    wp_add_inline_script('kv-main', <<<JS
(function(){
  try {
    var reduce = false;
    if (window.matchMedia) {
      reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    // Reveal animations: .animate-in -> add .is-in when visible
    var nodes = Array.prototype.slice.call(document.querySelectorAll('.animate-in'));
    if (nodes.length) {
      if (reduce || !('IntersectionObserver' in window)) {
        nodes.forEach(function(el){ el.classList.add('is-in'); });
      } else {
        var io = new IntersectionObserver(function(entries, obs){
          entries.forEach(function(e){
            if (!e.isIntersecting) return;
            e.target.classList.add('is-in');
            obs.unobserve(e.target);
          });
        }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
        nodes.forEach(function(el){ io.observe(el); });
      }
    }

    // Lazyload: img.lazyload[data-src] and srcset
    var imgs = Array.prototype.slice.call(document.querySelectorAll('img.lazyload[data-src], source.lazyload[data-srcset]'));
    function loadOne(el){
      if (!el || el.getAttribute('data-loaded')) return;
      if (el.tagName === 'IMG') {
        var s = el.getAttribute('data-src');
        if (s) el.setAttribute('src', s);
        var ss = el.getAttribute('data-srcset');
        if (ss) el.setAttribute('srcset', ss);
        el.removeAttribute('data-src');
        el.removeAttribute('data-srcset');
        el.setAttribute('loading','lazy');
        el.decoding = 'async';
      } else {
        var s2 = el.getAttribute('data-srcset');
        if (s2) el.setAttribute('srcset', s2);
        el.removeAttribute('data-srcset');
      }
      el.setAttribute('data-loaded','1');
      el.classList.remove('lazyload');
    }

    if (imgs.length) {
      if (!('IntersectionObserver' in window)) {
        imgs.forEach(loadOne);
      } else {
        var iol = new IntersectionObserver(function(entries, obs){
          entries.forEach(function(e){
            if (!e.isIntersecting) return;
            loadOne(e.target);
            obs.unobserve(e.target);
          });
        }, { threshold: 0.01, rootMargin: '200px 0px' });
        imgs.forEach(function(el){ iol.observe(el); });
      }
    }
  } catch(e) {}
})();
JS, 'after');
});

/* ===== CWV: Preload LCP hero image on front page ===== */
add_action('wp_head', function () {
    if (is_admin() || !is_front_page()) return;
    $hero = 'https://kvadratyra.ru/wp-content/uploads/2026/02/1771516545119-k15c0ipvzx.jpg';
    echo '<link rel="preload" as="image" href="' . esc_url($hero) . '" fetchpriority="high">' . "\n";
}, 0);

/* ===== CWV: Non-blocking Google Fonts via media print swap ===== */
add_filter('style_loader_tag', function ($tag, $handle) {
    if ($handle !== 'kv-google-fonts') return $tag;
    // Load fonts non-blocking: media="print" → onload switch to "all"
    $tag = str_replace(
        "media='all'",
        "media='print' onload=\"this.media='all'\"",
        $tag
    );
    return $tag;
}, 10, 2);

/* ===== CWV: Add defer to theme scripts ===== */
add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle !== 'kv-main') return $tag;
    if (strpos($tag, 'defer') !== false) return $tag;
    return str_replace(' src=', ' defer src=', $tag);
}, 10, 2);

/* Noindex search results (like mayai robots: Disallow /search/) */
add_action('wp_head', function () {
    if (is_admin()) return;
    if (!is_search()) return;
    echo '<meta name="robots" content="noindex,follow" />' . "\n";
}, 1);

/* ===== META DESCRIPTION (fallback) ===== */
function kv_build_meta_description(): string {
    // If Yoast has an explicit per-page description, reuse it.
    // Otherwise continue with fallback generation below.
    if (class_exists('WPSEO_Meta') && is_singular()) {
        $yoast_desc = trim((string) WPSEO_Meta::get_value('metadesc', get_the_ID()));
        if ($yoast_desc !== '') return $yoast_desc;
    }

    // GEO virtual pages output meta description in MU-plugin kv-geo (routing.php).
    // Avoid duplicate <meta name="description"> tags on /geo/*.
    if (function_exists('kv_geo_is_request') && kv_geo_is_request()) {
        return '';
    }

    // Front page
    if (is_front_page()) {
        $tagline = trim((string) get_bloginfo('description'));
        if ($tagline !== '') return $tagline;
        return 'Кровля, фасады и заборы под ключ. Бесплатный замер, смета и сроки по вашему объекту. Работаем по регионам — договор и гарантия.';
    }

    // Singular posts/pages
    if (is_singular()) {
        $desc = trim((string) get_the_excerpt());
        if ($desc === '') {
            $content = (string) get_post_field('post_content', get_the_ID());
            $desc = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($content)) ?? $content);
        }
        if ($desc !== '') return $desc;
    }

    // Term archives
    if (is_category() || is_tag() || is_tax()) {
        $obj = get_queried_object();
        if (is_object($obj) && !empty($obj->description)) {
            $desc = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string)$obj->description)) ?? (string)$obj->description);
            if ($desc !== '') return $desc;
        }
    }

    // Generic fallback
    $name = (string) get_bloginfo('name');
    $tagline = (string) get_bloginfo('description');
    return trim($tagline ?: $name);
}

function kv_meta_description_normalize(string $desc): string {
    $desc = trim((string)($desc ?? ''));
    if ($desc === '') return '';
    $desc = trim(preg_replace('/\s+/u', ' ', $desc) ?? $desc);
    if ($desc === '') return '';
    // Keep within snippet-friendly length
    if (function_exists('mb_substr')) return (string) mb_substr($desc, 0, 180);
    return (string) substr($desc, 0, 180);
}

// Buffer wp_head output so we can inject meta description only if missing.
add_action('wp_head', function () {
    if (is_admin()) return;
    // GEO virtual pages output description in MU-plugin kv-geo.
    if (function_exists('kv_geo_is_request') && kv_geo_is_request()) return;
    if (!isset($GLOBALS['__kv_head_meta_desc_buf'])) {
        $GLOBALS['__kv_head_meta_desc_buf'] = 1;
        ob_start();
    }
}, -9999);

add_action('wp_head', function () {
    if (is_admin()) return;
    if (function_exists('kv_geo_is_request') && kv_geo_is_request()) return;
    if (empty($GLOBALS['__kv_head_meta_desc_buf'])) return;

    $html = (string) ob_get_clean();
    $GLOBALS['__kv_head_meta_desc_buf'] = 0;

    $has_desc = (stripos($html, 'name="description"') !== false) || (stripos($html, "name='description'") !== false);
    if (!$has_desc) {
        $desc = kv_meta_description_normalize(kv_build_meta_description());
        if ($desc !== '') {
            $html .= '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        }
    }

    echo $html;
}, PHP_INT_MAX);

/* ===== FAVICON/ICONS (extra links for Yandex) ===== */
add_action('wp_head', function () {
    if (is_admin()) return;

    // Always expose explicit favicon links for crawlers.
    echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url(home_url('/favicon-16.png')) . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url(home_url('/favicon-32.png')) . '">' . "\n";
    echo '<link rel="icon" href="' . esc_url(home_url('/favicon.svg')) . '" type="image/svg+xml">' . "\n";
    echo '<link rel="shortcut icon" href="' . esc_url(home_url('/favicon.ico')) . '">' . "\n";

    // WP core prints site icon links (32x32, 192x192, apple-touch-icon, ms tile image).
    // Add extra, Yandex-friendly variants.
    if (function_exists('has_site_icon') && has_site_icon()) {
        echo '<link rel="icon" type="image/png" sizes="120x120" href="' . esc_url(home_url('/icon-120.png')) . '">' . "\n";
    }

    echo '<link rel="manifest" href="' . esc_url(home_url('/site.webmanifest')) . '">' . "\n";
}, 0);

/**
 * Generate a square PNG icon from the WordPress "Site Icon" attachment.
 *
 * @return array{path:string,url:string}|null
 */
function kv_site_icon_generate_png(int $size): ?array {
    if ($size <= 0) return null;

    $site_icon_id = (int) get_option('site_icon');
    if ($site_icon_id <= 0) return null;

    $src = get_attached_file($site_icon_id);
    if (!is_string($src) || $src === '' || !file_exists($src)) return null;

    $uploads = wp_upload_dir(null, false);
    if (!is_array($uploads) || !empty($uploads['error'])) return null;

    $mod = 0;
    if (function_exists('get_post_modified_time')) {
        $mod = (int) get_post_modified_time('U', true, $site_icon_id);
    }
    if ($mod <= 0) {
        $mod = (int) (@filemtime($src) ?: 0);
    }

    $subdir = 'site-icons-generated';
    $dir = trailingslashit((string) $uploads['basedir']) . $subdir;
    if (!wp_mkdir_p($dir)) return null;

    $filename = 'site-icon-' . $site_icon_id . '-' . $mod . '-' . $size . 'x' . $size . '.png';
    $path = trailingslashit($dir) . $filename;
    $url  = trailingslashit((string) $uploads['baseurl']) . $subdir . '/' . $filename;

    if (!file_exists($path)) {
        $editor = wp_get_image_editor($src);
        if (is_wp_error($editor)) return null;

        $resized = $editor->resize($size, $size, true);
        if (is_wp_error($resized)) return null;

        if (method_exists($editor, 'set_quality')) {
            $editor->set_quality(90);
        }

        $saved = $editor->save($path, 'image/png');
        if (is_wp_error($saved)) return null;
        if (!file_exists($path)) return null;
    }

    return ['path' => $path, 'url' => $url];
}

/* ===== FAVICONS (serve stable 200 for root URLs) ===== */
add_action('template_redirect', function () {
    if (is_admin()) return;

    $pathOnly = (string) (parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    if ($pathOnly === '') return;

    $themeFav = KV_THEME_DIR . '/assets/favicons';
    $map = [
        '/favicon.svg' => [$themeFav . '/favicon.svg', 'image/svg+xml; charset=utf-8'],
        '/icon-192.svg' => [$themeFav . '/icon-192.svg', 'image/svg+xml; charset=utf-8'],
        '/icon-512.svg' => [$themeFav . '/icon-512.svg', 'image/svg+xml; charset=utf-8'],
        '/site.webmanifest' => [$themeFav . '/site.webmanifest', 'application/manifest+json; charset=utf-8'],
    ];

    // Serve theme-hosted assets via root paths (works even if WP is in a subdir).
    if (isset($map[$pathOnly])) {
        [$file, $type] = $map[$pathOnly];
        if (!file_exists($file)) {
            status_header(404);
            exit;
        }
        status_header(200);
        header('Content-Type: ' . $type);
        header('Cache-Control: public, max-age=604800');
        readfile($file);
        exit;
    }

    // Yandex-friendly icon size (WP doesn't provide 120x120 out of the box).
    if ($pathOnly === '/icon-120.png') {
        $gen = kv_site_icon_generate_png(120);
        $file = is_array($gen) ? ($gen['path'] ?? '') : '';

        if (!is_string($file) || $file === '' || !file_exists($file)) {
            status_header(404);
            exit;
        }

        status_header(200);
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800');
        readfile($file);
        exit;
    }

    // Explicit PNG favicons for bots/snippets (Yandex often prefers 16/32 PNG).
    if ($pathOnly === '/favicon-16.png' || $pathOnly === '/favicon-32.png') {
        $size = ($pathOnly === '/favicon-16.png') ? 16 : 32;
        $gen = kv_site_icon_generate_png($size);
        $file = is_array($gen) ? ($gen['path'] ?? '') : '';

        // Fallback: WP core placeholder
        if (!is_string($file) || $file === '' || !file_exists($file)) {
            $file = ABSPATH . WPINC . '/images/w-logo-blue-white-bg.png';
        }
        if (!file_exists($file)) {
            status_header(404);
            exit;
        }

        status_header(200);
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800');
        readfile($file);
        exit;
    }

    // Keep /favicon.ico stable for crawlers that still request it.
    if ($pathOnly === '/favicon.ico') {
        // First try to serve the actual favicon.ico if it exists in the theme
        $themeFaviconIco = KV_THEME_DIR . '/assets/favicons/favicon.ico';
        if (file_exists($themeFaviconIco)) {
            status_header(200);
            header('Content-Type: image/x-icon');
            header('Cache-Control: public, max-age=604800');
            readfile($themeFaviconIco);
            exit;
        }

        // Fallback to PNG if no ico file exists
        $gen = kv_site_icon_generate_png(32);
        $file = is_array($gen) ? ($gen['path'] ?? '') : '';

        // Fallback: WP core placeholder (rare; only if no site icon / editor failure).
        if (!is_string($file) || $file === '' || !file_exists($file)) {
            $file = ABSPATH . WPINC . '/images/w-logo-blue-white-bg.png';
        }

        if (!file_exists($file)) {
            status_header(404);
            exit;
        }
        status_header(200);
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800');
        readfile($file);
        exit;
    }
}, 0);

/**
 * Flush rewrite rules once in admin to ensure new routes work even if theme was already active
 * (guides CPT/taxonomy + ai-feed endpoint).
 */
add_action('admin_init', function () {
    $rewrite_version = 2;
    if ((int) get_option('kv_theme_rewrite_flushed_version', 0) >= $rewrite_version) return;
    if (!current_user_can('manage_options')) return;
    flush_rewrite_rules(false);
    update_option('kv_theme_rewrite_flushed', '1', false);
    update_option('kv_theme_rewrite_flushed_version', $rewrite_version, false);
});

// Fallback: one-shot rewrite flush on frontend after deploy, if admin has not visited yet.
add_action('init', function () {
    $rewrite_version = 2;
    if ((int) get_option('kv_theme_rewrite_flushed_version', 0) >= $rewrite_version) return;
    flush_rewrite_rules(false);
    update_option('kv_theme_rewrite_flushed', '1', false);
    update_option('kv_theme_rewrite_flushed_version', $rewrite_version, false);
}, 99);

/* ===== ADMIN: Fix tagline + Site Icon SEO fields (one-time) ===== */
add_action('init', function () {
    if (!is_user_logged_in()) return;
    if (!current_user_can('manage_options')) return;

    $desired_tagline = 'Кровля, фасады, заборы — замер, доставка, монтаж';
    if (get_option('kv_tagline_fixed', '') !== '1') {
        update_option('blogdescription', $desired_tagline, false);
        update_option('kv_tagline_fixed', '1', false);
    }

    $site_icon_id = (int) get_option('site_icon');
    if ($site_icon_id <= 0) return;

    $done_for = (int) get_option('kv_site_icon_seo_updated_for', 0);
    if ($done_for === $site_icon_id) return;

    $alt = 'Квадратура — значок сайта (логотип): кровля, фасады, заборы';
    $title = 'Значок сайта «Квадратура» — кровля, фасады, заборы';
    $caption = 'Фавикон и логотип компании «Квадратура»';
    $description = 'Официальный значок сайта компании «Квадратура». Кровля, фасады, заборы — замер, доставка, монтаж.';

    update_post_meta($site_icon_id, '_wp_attachment_image_alt', $alt);
    wp_update_post([
        'ID'           => $site_icon_id,
        'post_title'   => $title,
        'post_excerpt' => $caption,
        'post_content' => $description,
    ]);

    update_option('kv_site_icon_seo_updated_for', $site_icon_id, false);
}, 6);

/* ===== CLEANUP HEAD ===== */
add_action('init', function () {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_resource_hints', 2);
    remove_action('wp_head', 'feed_links_extra', 3);

    // CWV: stop WP global styles inline CSS (theme is classic, blocks not required).
    // These actions are typically attached with priority=1, so remove with the same priority.
    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles', 1);
    remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles', 1);
    remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
    // Extra safety if WP hooks differ by version:
    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
    remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles');
    remove_action('wp_footer', 'wp_enqueue_global_styles');
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
});

// Ensure REST oEmbed endpoint exists (some optimizers remove it, causing /wp-json/oembed/... 404).
add_action('rest_api_init', function () {
    if (function_exists('wp_oembed_register_route')) {
        wp_oembed_register_route();
    }
}, 5);

// Some crawlers (including webmaster tools) probe oEmbed for URLs that WP may treat as non-embeddable (e.g. home page),
// which results in 404. Convert such 404 into a minimal valid oEmbed response for our own domain.
add_filter('rest_post_dispatch', function ($response, $server, $request) {
    if (!is_object($request) || !method_exists($request, 'get_route')) return $response;
    $route = (string) $request->get_route();
    if ($route !== '/oembed/1.0/embed') return $response;

    if (!is_object($response) || !method_exists($response, 'get_status')) return $response;
    if ((int) $response->get_status() !== 404) return $response;

    $url = '';
    if (method_exists($request, 'get_param')) {
        $url = (string) $request->get_param('url');
    }
    $host = $url ? (string) (parse_url($url, PHP_URL_HOST) ?? '') : '';
    if ($host === '' || !preg_match('/(^|\\.)kvadratyra\\.ru$/i', $host)) return $response;

    if (!class_exists('WP_REST_Response')) return $response;
    $data = [
        'version' => '1.0',
        'type' => 'link',
        'provider_name' => (string) get_bloginfo('name'),
        'provider_url' => (string) home_url('/'),
        'title' => (string) wp_get_document_title(),
        'url' => $url,
    ];
    return new WP_REST_Response($data, 200);
}, 10, 3);

/* Remove emoji scripts */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
});

/* ===== BREADCRUMBS ===== */
if (!function_exists('kv_breadcrumbs')) {
    function kv_breadcrumbs() {
        if (is_front_page()) return;

        $sep = '<span class="breadcrumbs__sep">/</span>';
        $out = '<nav class="breadcrumbs" aria-label="Навигация">';
        $out .= '<a href="' . esc_url(home_url('/')) . '">Главная</a>' . $sep;

        if (is_single()) {
            // Guides: /guides/ -> topic -> title
            if (get_post_type() === 'guide') {
                $out .= '<a href="' . esc_url(home_url('/guides/')) . '">Гайды</a>' . $sep;
                $topics = get_the_terms(get_the_ID(), 'guide_topic');
                if ($topics && !is_wp_error($topics)) {
                    $t = $topics[0];
                    $out .= '<a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a>' . $sep;
                }
            } else {
                $cats = get_the_category();
                if ($cats) {
                    $cat = $cats[0];
                    $out .= '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>' . $sep;
                }
            }
            $out .= '<span>' . esc_html(get_the_title()) . '</span>';
        } elseif (is_page()) {
            $out .= '<span>' . esc_html(get_the_title()) . '</span>';
        } elseif (is_category()) {
            $out .= '<span>' . esc_html(single_cat_title('', false)) . '</span>';
        } elseif (is_tag()) {
            $out .= '<span>' . esc_html(single_tag_title('', false)) . '</span>';
        } elseif (is_post_type_archive('guide')) {
            $out .= '<span>Гайды</span>';
        } elseif (is_tax('guide_topic')) {
            $out .= '<a href="' . esc_url(home_url('/guides/')) . '">Гайды</a>' . $sep;
            $out .= '<span>' . esc_html(single_term_title('', false)) . '</span>';
        } elseif (is_search()) {
            $out .= '<span>Поиск: ' . esc_html(get_search_query()) . '</span>';
        } elseif (is_404()) {
            $out .= '<span>404</span>';
        } elseif (is_archive()) {
            $out .= '<span>' . esc_html(get_the_archive_title()) . '</span>';
        }

        $out .= '</nav>';
        echo $out;
    }
}

/* ===== READING TIME ===== */
if (!function_exists('kv_reading_time')) {
    function kv_reading_time($post_id = null) {
        if (!$post_id) $post_id = get_the_ID();
        $content = get_post_field('post_content', $post_id);
        $word_count = str_word_count(strip_tags($content));
        $reading_time = max(1, ceil($word_count / 200));
        return $reading_time . ' мин чтения';
    }
}

/* ===== EXCERPT LENGTH ===== */
add_filter('excerpt_length', function () { return 25; });
add_filter('excerpt_more', function () { return '&hellip;'; });

/* ===== CUSTOM MENU WALKER (clean output) ===== */
class KV_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $class_names = join(' ', array_filter($classes));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<a' . $class_names . ' href="' . esc_url($item->url) . '">';
        $output .= esc_html($item->title);
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</a>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
}

/* ===== PHONE HELPER ===== */
if (!function_exists('kv_phone')) {
    function kv_phone($formatted = true) {
        $phone = get_option('kv_phone', KV_DEFAULT_PHONE);
        if (!$formatted) {
            return preg_replace('/[^+\d]/', '', $phone);
        }
        return $phone;
    }
}

/* ===== ADMIN: THEME OPTIONS ===== */
add_action('admin_menu', function () {
    add_theme_page(
        'Настройки Kvadratyra',
        'Kvadratyra',
        'manage_options',
        'kv-settings',
        'kv_settings_page'
    );
});

function kv_settings_page() {
    if (isset($_POST['kv_save_settings']) && wp_verify_nonce($_POST['_kv_nonce'], 'kv_settings')) {
        update_option('kv_phone', sanitize_text_field($_POST['kv_phone'] ?? ''));
        update_option('kv_email', sanitize_email($_POST['kv_email'] ?? ''));
        update_option('kv_telegram', sanitize_text_field($_POST['kv_telegram'] ?? ''));
        update_option('kv_max_chat_url', esc_url_raw($_POST['kv_max_chat_url'] ?? ''));
        update_option('kv_ok_group_url', esc_url_raw($_POST['kv_ok_group_url'] ?? ''));
        update_option('kv_yandex_business_url', esc_url_raw($_POST['kv_yandex_business_url'] ?? ''));
        update_option('kv_whatsapp', esc_url_raw($_POST['kv_whatsapp'] ?? ''));
        update_option('kv_address', sanitize_text_field($_POST['kv_address'] ?? ''));
        update_option('kv_company', sanitize_text_field($_POST['kv_company'] ?? ''));
        update_option('kv_inn', preg_replace('/[^\d]/', '', (string)($_POST['kv_inn'] ?? '')));
        update_option('kv_ogrn', preg_replace('/[^\d]/', '', (string)($_POST['kv_ogrn'] ?? '')));
        update_option('kv_work_hours', sanitize_text_field($_POST['kv_work_hours'] ?? ''));
        update_option('kv_expert_photo', esc_url_raw($_POST['kv_expert_photo'] ?? ''));
        update_option('kv_enable_lottie', isset($_POST['kv_enable_lottie']) ? 1 : 0);
        update_option('kv_yandex_metrika', sanitize_text_field($_POST['kv_yandex_metrika'] ?? ''));
        echo '<div class="notice notice-success"><p>Настройки сохранены.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Настройки Kvadratyra</h1>
        <form method="post">
            <?php wp_nonce_field('kv_settings', '_kv_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>Телефон</th>
                    <td><input type="text" name="kv_phone" value="<?php echo esc_attr(get_option('kv_phone', KV_DEFAULT_PHONE)); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><input type="email" name="kv_email" value="<?php echo esc_attr(get_option('kv_email', '')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Telegram</th>
                    <td><input type="text" name="kv_telegram" value="<?php echo esc_attr(get_option('kv_telegram', '')); ?>" class="regular-text" placeholder="https://t.me/username"></td>
                </tr>
                <tr>
                    <th>MAX (ссылка на чат)</th>
                    <td>
                        <input type="text" name="kv_max_chat_url" value="<?php echo esc_attr(get_option('kv_max_chat_url', KV_DEFAULT_MAX_CHAT)); ?>" class="regular-text">
                        <p class="description">Ссылка-приглашение в чат MAX. Будет показана в статьях и в контактах.</p>
                    </td>
                </tr>
                <tr>
                    <th>Одноклассники (группа)</th>
                    <td>
                        <input type="text" name="kv_ok_group_url" value="<?php echo esc_attr(get_option('kv_ok_group_url', KV_DEFAULT_OK_GROUP)); ?>" class="regular-text">
                        <p class="description">Ссылка на группу в ОК. Будет показана в статьях и в контактах.</p>
                    </td>
                </tr>
                <tr>
                    <th>Яндекс.Бизнес (ссылка на профиль)</th>
                    <td>
                        <input type="text" name="kv_yandex_business_url" value="<?php echo esc_attr(get_option('kv_yandex_business_url', '')); ?>" class="regular-text" placeholder="https://yandex.ru/profile/XXXXXXXXXXX">
                        <p class="description">Ссылка на карточку организации в Яндекс.Бизнес/Картах. Используется в Schema (`sameAs`) для траста и региональности.</p>
                    </td>
                </tr>
                <tr>
                    <th>WhatsApp</th>
                    <td><input type="text" name="kv_whatsapp" value="<?php echo esc_attr(get_option('kv_whatsapp', '')); ?>" class="regular-text" placeholder="https://wa.me/79001234567"></td>
                </tr>
                <tr>
                    <th>Адрес</th>
                    <td><input type="text" name="kv_address" value="<?php echo esc_attr(get_option('kv_address', '')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Организация (как в договоре)</th>
                    <td><input type="text" name="kv_company" value="<?php echo esc_attr(get_option('kv_company', '')); ?>" class="regular-text" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>"></td>
                </tr>
                <tr>
                    <th>ИНН</th>
                    <td><input type="text" name="kv_inn" value="<?php echo esc_attr(get_option('kv_inn', '')); ?>" class="regular-text" placeholder="1234567890"></td>
                </tr>
                <tr>
                    <th>ОГРН / ОГРНИП</th>
                    <td><input type="text" name="kv_ogrn" value="<?php echo esc_attr(get_option('kv_ogrn', '')); ?>" class="regular-text" placeholder="1234567890123"></td>
                </tr>
                <tr>
                    <th>Режим работы</th>
                    <td><input type="text" name="kv_work_hours" value="<?php echo esc_attr(get_option('kv_work_hours', 'Пн–Сб: 09:00–19:00')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Фото эксперта (URL)</th>
                    <td>
                        <input type="text" name="kv_expert_photo" value="<?php echo esc_attr(get_option('kv_expert_photo', '')); ?>" class="regular-text" placeholder="https://kvadratyra.ru/wp-content/uploads/2026/02/your-photo.jpg">
                        <p class="description">Загрузите фото в «Медиафайлы» → откройте файл → скопируйте URL и вставьте сюда. Используется в блоке «Эксперт» на главной.</p>
                    </td>
                </tr>
                <tr>
                    <th>Lottie (эксперимент)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="kv_enable_lottie" <?php checked((int)get_option('kv_enable_lottie', 0), 1); ?>>
                            Включить Lottie-иконки на главной (может влиять на скорость)
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>Яндекс.Метрика ID</th>
                    <td><input type="text" name="kv_yandex_metrika" value="<?php echo esc_attr(get_option('kv_yandex_metrika', '')); ?>" class="regular-text" placeholder="12345678"></td>
                </tr>
            </table>
            <p><input type="submit" name="kv_save_settings" class="button-primary" value="Сохранить"></p>
        </form>
    </div>
    <?php
}

/* ===== YANDEX METRIKA ===== */
add_action('wp_head', function () {
    $id = get_option('kv_yandex_metrika', '106850458');
    $id = preg_replace('/[^\d]/', '', (string)$id);
    if (empty($id) || is_admin()) return;
    ?>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=<?php echo esc_js($id); ?>', 'ym');

        ym(<?php echo esc_js($id); ?>, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/<?php echo esc_attr($id); ?>" style="position:absolute;left:-9999px;" alt="" /></div></noscript>
    <?php
}, 1);

/* ===== SCHEMA.ORG OUTPUT ===== */
add_action('wp_head', function () {
    if (is_admin()) return;

    // Avoid outputting homepage schema on virtual GEO pages.
    if (function_exists('kv_geo_is_request') && kv_geo_is_request()) {
        // GEO pages output their own schema in MU-plugin kv-geo (routing.php).
    } elseif (is_front_page() && function_exists('kv_schema_graph_home')) {
        echo kv_schema_graph_home() . "\n";
    }

    if (is_singular(['post', 'guide']) && function_exists('kv_schema_article')) {
        echo kv_schema_article(get_the_ID()) . "\n";
    }
}, 2);

/* ===== DISABLE GUTENBERG ON FRONT PAGE ===== */
add_filter('use_block_editor_for_post', function ($use, $post) {
    if ($post && $post->ID === (int) get_option('page_on_front')) {
        return false;
    }
    return $use;
}, 10, 2);

/* Preconnect/dns-prefetch are now in header.php (before wp_head) for earlier discovery. */

/* ===== CWV: Add loading="lazy" and decoding="async" to content images ===== */
add_filter('wp_get_attachment_image_attributes', function ($attr) {
    if (!isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    return $attr;
});

/* ===== CWV: Disable WP global styles inline (reduce CLS) ===== */
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('global-styles');
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('classic-theme-styles');
}, 100);

/* ===== ALLOW SVG UPLOAD ===== */
add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    return $mimes;
});

/* ===== REGISTER WIDGET AREAS ===== */
add_action('widgets_init', function () {
    register_sidebar([
        'name'          => 'Сайдбар',
        'id'            => 'sidebar-1',
        'before_widget' => '<div class="widget card">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
});

/* ===== AUTO-SET YANDEX BUSINESS URL (migration) ===== */
add_action('init', function () {
    $current = get_option('kv_yandex_business_url', '');
    $correct = 'https://yandex.ru/profile/13123153536';
    if ($current === '' || strpos($current, '155646303209') !== false) {
        update_option('kv_yandex_business_url', $correct);
    }
}, 5);

/* ===== OPEN GRAPH + TWITTER CARD META TAGS ===== */
add_action('wp_head', function () {
    if (is_admin()) return;
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION')) return;
    if (function_exists('kv_geo_is_request') && kv_geo_is_request()) return;

    $site_name = get_bloginfo('name') ?: 'Квадратура';
    $locale    = 'ru_RU';

    if (is_front_page()) {
        $og_title = $site_name . ' — кровля, фасады и заборы под ключ';
        $og_desc  = kv_build_meta_description();
        $og_url   = home_url('/');
        $og_type  = 'website';
        $og_image = 'https://kvadratyra.ru/wp-content/uploads/2026/02/1771516545119-k15c0ipvzx.jpg';
    } elseif (is_singular()) {
        $og_title = get_the_title() . ' — ' . $site_name;
        $og_desc  = kv_build_meta_description();
        $og_url   = get_permalink();
        $og_type  = (is_page() ? 'website' : 'article');
        $og_image = '';
        if (has_post_thumbnail()) {
            $og_image = get_the_post_thumbnail_url(get_the_ID(), 'kv-hero');
        }
        if (!$og_image) {
            $og_image = 'https://kvadratyra.ru/wp-content/uploads/2026/02/1771516545119-k15c0ipvzx.jpg';
        }
    } elseif (is_archive() || is_category() || is_tag() || is_tax()) {
        $og_title = wp_get_document_title();
        $og_desc  = kv_build_meta_description();
        $og_url   = (is_category() ? get_category_link(get_queried_object_id()) : get_term_link(get_queried_object()));
        if (is_wp_error($og_url)) $og_url = home_url($_SERVER['REQUEST_URI'] ?? '/');
        $og_type  = 'website';
        $og_image = 'https://kvadratyra.ru/wp-content/uploads/2026/02/1771516545119-k15c0ipvzx.jpg';
    } else {
        return;
    }

    if (!$og_desc) {
        $og_desc = 'Кровля, фасады и заборы под ключ. Бесплатный замер, смета и сроки.';
    }

    echo '<!-- Open Graph -->' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr($locale) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($og_desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($og_url) . '">' . "\n";
    if ($og_image) {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="600">' . "\n";
    }
    echo '<!-- Twitter Card -->' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($og_title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($og_desc) . '">' . "\n";
    if ($og_image) {
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
    }
}, 3);

/* ===== ROBOTS.TXT (Clean-param, Host, Sitemap) ===== */
add_filter('robots_txt', function ($output, $public) {
    $custom  = "User-agent: *\n";
    $custom .= "Disallow: /wp-admin/\n";
    $custom .= "Allow: /wp-admin/admin-ajax.php\n";
    $custom .= "Disallow: /wp-includes/\n";
    $custom .= "Disallow: /?s=\n";
    $custom .= "Disallow: /search/\n";
    $custom .= "Disallow: /cart/\n";
    $custom .= "Disallow: /checkout/\n";
    $custom .= "Disallow: /*?utm_*\n";
    $custom .= "Disallow: /*?yclid*\n";
    $custom .= "Disallow: /*?gclid*\n";
    $custom .= "\n";
    $custom .= "Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term /\n";
    $custom .= "Clean-param: yclid /\n";
    $custom .= "Clean-param: gclid /\n";
    $custom .= "Clean-param: fbclid /\n";
    $custom .= "\n";
    $custom .= "User-agent: Yandex\n";
    $custom .= "Disallow: /wp-admin/\n";
    $custom .= "Allow: /wp-admin/admin-ajax.php\n";
    $custom .= "Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term /\n";
    $custom .= "Clean-param: yclid /\n";
    $custom .= "\n";
    // Yandex Host directive expects host without scheme.
    $custom .= "Host: kvadratyra.ru\n";
    $custom .= "Sitemap: https://kvadratyra.ru/wp-sitemap.xml\n";
    $custom .= "Sitemap: https://kvadratyra.ru/kv-geo-sitemap-cities.xml\n";
    $custom .= "Sitemap: https://kvadratyra.ru/kv-geo-sitemap-services.xml\n";
    $custom .= "Sitemap: https://kvadratyra.ru/feed/dzen/\n";

    return $custom;
}, 10, 2);

/* ===== CANONICAL (fallback, when no SEO plugin handles it) ===== */
add_action('wp_head', function () {
    if (is_admin()) return;
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION')) return;
    if (function_exists('kv_geo_is_request') && kv_geo_is_request()) return;
    if (is_404()) return;

    $canonical = '';
    if (is_front_page()) {
        $canonical = home_url('/');
    } elseif (is_singular()) {
        $canonical = get_permalink();
    } elseif (is_home()) {
        $canonical = get_permalink((int) get_option('page_for_posts')) ?: home_url('/');
    } elseif (is_category() || is_tag() || is_tax() || is_post_type_archive() || is_date() || is_author()) {
        $canonical = get_pagenum_link(max(1, (int) get_query_var('paged')));
    } else {
        $canonical = home_url((string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/'));
    }

    if (!$canonical) return;
    echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";
}, 4);

/* ===== INCLUDE GUIDE CPT IN MAIN RSS FEED (for Dzen) ===== */
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) return;
    if ($query->is_feed()) {
        $query->set('post_type', ['post', 'guide']);
    }
});

/* ===== DZEN RSS FEED WITH FULL CONTENT ===== */
add_action('init', function () {
    add_feed('dzen', 'kv_dzen_feed_render');
    // Extra safety: bind feed action explicitly in case another plugin alters feed hooks.
    add_action('do_feed_dzen', 'kv_dzen_feed_render', 10, 2);
}, 20);

function kv_dzen_feed_render() {
    $posts = get_posts([
        'post_type'      => ['post', 'guide'],
        'post_status'    => 'publish',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
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
    setup_postdata($p);
    $content = apply_filters('the_content', $p->post_content);
    $content = str_replace(']]>', ']]&gt;', $content);
    $excerpt = has_excerpt($p->ID) ? get_the_excerpt($p) : wp_trim_words(strip_tags($content), 40, '...');
    $thumb   = get_the_post_thumbnail_url($p->ID, 'kv-hero');
?>
<item>
<title><?php echo esc_html(get_the_title($p)); ?></title>
<link><?php echo esc_url(get_permalink($p)); ?></link>
<guid isPermaLink="true"><?php echo esc_url(get_permalink($p)); ?></guid>
<pubDate><?php echo esc_html(get_the_date('D, d M Y H:i:s +0000', $p)); ?></pubDate>
<dc:creator><?php echo esc_html(get_the_author_meta('display_name', $p->post_author)); ?></dc:creator>
<description><![CDATA[<?php echo $excerpt; ?>]]></description>
<content:encoded><![CDATA[<?php
    if ($thumb) echo '<img src="' . esc_url($thumb) . '" alt="' . esc_attr(get_the_title($p)) . '" />';
    echo $content;
?>]]></content:encoded>
<?php
    $cats = get_the_category($p->ID);
    if (!$cats || is_wp_error($cats)) $cats = [];
    $terms = get_the_terms($p->ID, 'guide_topic');
    if ($terms && !is_wp_error($terms)) $cats = array_merge($cats, $terms);
    foreach ($cats as $c) :
?>
<category><?php echo esc_html($c->name); ?></category>
<?php endforeach; ?>
</item>
<?php endforeach; wp_reset_postdata(); ?>
</channel>
</rss>
<?php
    exit;
}

/* ===== DZEN FEED FALLBACK ROUTE (works even before rewrite flush) ===== */
add_action('template_redirect', function () {
    $pathOnly = (string) (parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    if (strpos($pathOnly, '/feed/dzen') !== 0) return;
    if (function_exists('kv_dzen_feed_render')) {
        kv_dzen_feed_render();
    }
}, 0);

/* Keep canonical /feed/dzen/ as RSS endpoint (no WP canonical redirect to pages). */
add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
    $pathOnly = (string) (parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    if (strpos($pathOnly, '/feed/dzen') === 0) {
        return false;
    }
    return $redirect_url;
}, 10, 2);

/* Earliest route interception to guarantee RSS output for /feed/dzen/. */
add_action('parse_request', function () {
    $pathOnly = (string) (parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    if (strpos($pathOnly, '/feed/dzen') !== 0) return;
    if (function_exists('kv_dzen_feed_render')) {
        kv_dzen_feed_render();
    }
}, 0);

/* ===== YATI/Neuro: add structured Q&A blocks to core service pages ===== */
add_filter('the_content', function ($content) {
    if (is_admin() || wp_doing_ajax() || is_feed()) return $content;
    if (!is_page()) return $content;
    if (strpos((string)$content, 'data-kv-neuro="1"') !== false) return $content;

    global $post;
    if (!$post instanceof WP_Post) return $content;
    $slug = (string)($post->post_name ?? '');
    $slug = sanitize_title($slug);
    if (!in_array($slug, ['krovlya', 'fasady', 'zabory'], true)) return $content;
    if (!function_exists('kv_geo_find_service')) return $content;

    $svc = kv_geo_find_service($slug);
    if (!is_array($svc)) return $content;

    $name = (string)($svc['name'] ?? '');
    $faq = (array)($svc['faq'] ?? []);
    $pricing = (array)($svc['pricing_table'] ?? []);

    $html = '<section class="card" data-kv-neuro="1" style="margin-top:22px;">';
    $html .= '<h2 style="margin:0 0 10px;">Короткий ответ по услуге: ' . esc_html($name) . '</h2>';
    $html .= '<p style="margin:0 0 10px;">Работаем по договору, делаем бесплатный замер и даём смету с понятными этапами. Ниже — ориентиры по цене и ответы на частые вопросы, чтобы Яндекс и пользователи видели «сразу по делу».</p>';

    if (!empty($pricing)) {
        $html .= '<h2 style="margin:22px 0 10px;">Ориентиры по цене</h2>';
        $html .= '<div class="entry-content"><table><thead><tr><th>Работа</th><th>От</th><th>Комментарий</th></tr></thead><tbody>';
        foreach ($pricing as $row) {
            if (!is_array($row)) continue;
            $rName = (string)($row['name'] ?? '');
            $from = $row['from'] ?? '';
            $unit = (string)($row['unit'] ?? '');
            $note = (string)($row['note'] ?? '');
            if ($rName === '' || $from === '' || $unit === '') continue;
            $html .= '<tr><td>' . esc_html($rName) . '</td><td><strong>' . esc_html((string)$from) . ' ' . esc_html($unit) . '</strong></td><td>' . esc_html($note) . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
    }

    if (!empty($faq)) {
        $html .= '<h2 style="margin:22px 0 10px;">Вопросы и ответы</h2>';
        $html .= '<div class="entry-content">';
        foreach ($faq as $item) {
            if (!is_array($item)) continue;
            $q = trim((string)($item['q'] ?? ''));
            $a = trim((string)($item['a'] ?? ''));
            if ($q === '' || $a === '') continue;
            $html .= '<h3>' . esc_html($q) . '</h3>';
            $html .= '<p>' . esc_html($a) . '</p>';
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $content . $html;
}, 20);
