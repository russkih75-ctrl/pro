<?php
/**
 * Plugin Name: KV GEO Loader
 * Description: Loader for kv-geo MU plugin (keeps logic in a subdirectory).
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

$kv_geo_main = __DIR__ . '/kv-geo/kv-geo.php';
if (file_exists($kv_geo_main)) {
  require_once $kv_geo_main;
}

