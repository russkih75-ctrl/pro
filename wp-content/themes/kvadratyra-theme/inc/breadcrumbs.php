<?php
/**
 * Schema.org BreadcrumbList markup
 *
 * @package Kvadratyra
 */

if (!defined('ABSPATH')) exit;

/**
 * Output BreadcrumbList Schema.org JSON-LD.
 */
add_action('wp_head', function () {
    if (is_front_page() || is_admin()) return;

    $items = [];
    $pos = 1;

    $items[] = [
        '@type'    => 'ListItem',
        'position' => $pos++,
        'name'     => 'Главная',
        'item'     => home_url('/'),
    ];

    if (is_single()) {
        // Guides: /guides/ -> topic -> title
        if (get_post_type() === 'guide') {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => 'Гайды',
                'item'     => home_url('/guides/'),
            ];
            $topics = get_the_terms(get_the_ID(), 'guide_topic');
            if ($topics && !is_wp_error($topics)) {
                $t = $topics[0];
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => $t->name,
                    'item'     => get_term_link($t),
                ];
            }
        } else {
            $cats = get_the_category();
            if ($cats) {
                $cat = $cats[0];
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => $cat->name,
                    'item'     => get_category_link($cat->term_id),
                ];
            }
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
        ];
    } elseif (is_page()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
        ];
    } elseif (is_category()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => single_cat_title('', false),
        ];
    } elseif (is_post_type_archive('guide')) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Гайды',
            'item'     => home_url('/guides/'),
        ];
    } elseif (is_tax('guide_topic')) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Гайды',
            'item'     => home_url('/guides/'),
        ];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => single_term_title('', false),
        ];
    }

    if (count($items) < 2) return;

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
});
