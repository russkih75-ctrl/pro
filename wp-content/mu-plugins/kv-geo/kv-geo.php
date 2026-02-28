<?php
/**
 * Plugin Name: KV GEO
 * Description: GEO data model + validators for Kvadratyra (cities/regions/intents). Routing/sitemaps/indexnow are added in later steps.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

define('KV_GEO_VERSION', '1.0.0');
define('KV_GEO_DIR', __DIR__);

require_once KV_GEO_DIR . '/lib/data.php';
require_once KV_GEO_DIR . '/lib/semantic.php';
require_once KV_GEO_DIR . '/lib/validators.php';
require_once KV_GEO_DIR . '/lib/routing.php';
require_once KV_GEO_DIR . '/lib/indexnow.php';
require_once KV_GEO_DIR . '/lib/sitemaps.php';

add_action('init', function () {
  // Namespaced taxonomies to avoid collisions with other plugins/themes.
  register_taxonomy('kv_region', ['post', 'page'], [
    'label' => 'Регионы (KV)',
    'public' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'hierarchical' => true,
  ]);

  register_taxonomy('kv_city', ['post', 'page'], [
    'label' => 'Города/районы (KV)',
    'public' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'hierarchical' => true,
  ]);

  register_taxonomy('kv_intent', ['post', 'page'], [
    'label' => 'Интенты (KV)',
    'public' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'hierarchical' => true,
  ]);
});

// Global defaults (can be overridden later via settings UI).
add_action('init', function () {
  if (get_option('kv_geo_min_unique_score', null) === null) {
    add_option('kv_geo_min_unique_score', 40, '', false);
  }
}, 11);

