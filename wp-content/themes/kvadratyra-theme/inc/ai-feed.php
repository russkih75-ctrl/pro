<?php
/**
 * AI-feed (JSON) — компактный «слепок» сайта для AI/ботов и быстрых интеграций.
 * URL: /ai-feed.json
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    add_rewrite_rule('^ai-feed\.json$', 'index.php?kv_ai_feed=1', 'top');
}, 21);

add_filter('query_vars', function ($vars) {
    $vars[] = 'kv_ai_feed';
    return $vars;
});

add_action('template_redirect', function () {
    if (!get_query_var('kv_ai_feed')) return;

    status_header(200);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Robots-Tag: noindex');

    $site = home_url('/');
    $out = [
        'generated_at' => gmdate('c'),
        'site' => [
            'name' => get_bloginfo('name'),
            'url'  => $site,
            'description' => get_bloginfo('description'),
            'phone' => function_exists('kv_phone') ? kv_phone() : '',
            'email' => get_option('kv_email', ''),
            'telegram' => get_option('kv_telegram', ''),
        ],
        'hubs' => [
            ['type' => 'home', 'url' => $site],
            ['type' => 'geo', 'url' => home_url('/geo/')],
            ['type' => 'guides', 'url' => home_url('/guides/')],
            ['type' => 'blog', 'url' => home_url('/blog/')],
        ],
        'services' => function_exists('kv_geo_get_services') ? kv_geo_get_services() : [],
        'intents'  => function_exists('kv_geo_get_intents') ? kv_geo_get_intents() : [],
        'items' => [],
    ];

    // Latest blog posts
    $posts = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'numberposts'    => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'suppress_filters' => false,
    ]);
    foreach ($posts as $p) {
        $out['items'][] = [
            'type' => 'post',
            'url'  => get_permalink($p),
            'title' => get_the_title($p),
            'excerpt' => wp_trim_words(get_the_excerpt($p), 30, '...'),
            'dateModified' => get_the_modified_date('c', $p),
            'categories' => array_map(function ($c) { return $c->name; }, get_the_category($p->ID) ?: []),
        ];
    }

    // Latest guides (if CPT exists)
    $guides = get_posts([
        'post_type'      => 'guide',
        'post_status'    => 'publish',
        'numberposts'    => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'suppress_filters' => false,
    ]);
    foreach ($guides as $g) {
        $topics = get_the_terms($g->ID, 'guide_topic');
        $out['items'][] = [
            'type' => 'guide',
            'url'  => get_permalink($g),
            'title' => get_the_title($g),
            'excerpt' => wp_trim_words(get_the_excerpt($g), 30, '...'),
            'dateModified' => get_the_modified_date('c', $g),
            'topics' => $topics && !is_wp_error($topics) ? array_map(function ($t) { return $t->name; }, $topics) : [],
        ];
    }

    echo wp_json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}, 0);

