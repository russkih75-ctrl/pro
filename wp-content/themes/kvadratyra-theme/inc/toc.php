<?php
/**
 * Table of Contents generator
 * Extracts H2/H3 headings from content and generates a sticky sidebar TOC.
 *
 * @package Kvadratyra
 */

if (!defined('ABSPATH')) exit;

/**
 * Parse content and add IDs to headings, return TOC data.
 *
 * @param string $content Post content HTML.
 * @return array ['content' => modified_html, 'items' => array of TOC items]
 */
function kv_generate_toc($content) {
    if (empty($content)) return ['content' => $content, 'items' => []];

    $items = [];
    $index = 0;

    $content = preg_replace_callback(
        '/<(h[23])([^>]*)>(.*?)<\/h[23]>/is',
        function ($matches) use (&$items, &$index) {
            $tag   = $matches[1];
            $attrs = $matches[2];
            $text  = strip_tags($matches[3]);
            $index++;

            // Generate slug
            $slug = 'section-' . $index;

            // Check if already has an ID
            if (preg_match('/id=["\']([^"\']+)["\']/', $attrs, $id_match)) {
                $slug = $id_match[1];
            } else {
                $attrs .= ' id="' . esc_attr($slug) . '"';
            }

            $items[] = [
                'id'    => $slug,
                'text'  => $text,
                'level' => $tag, // h2 or h3
            ];

            return '<' . $tag . $attrs . '>' . $matches[3] . '</' . $tag . '>';
        },
        $content
    );

    return [
        'content' => $content,
        'items'   => $items,
    ];
}

/**
 * Render TOC HTML from items array.
 *
 * @param array $items TOC items from kv_generate_toc().
 * @return string TOC HTML
 */
function kv_render_toc($items) {
    if (empty($items)) return '';

    $html = '<aside class="toc card" aria-label="Оглавление">';
    $html .= '<div class="toc__title">Содержание</div>';

    foreach ($items as $item) {
        $class = 'toc--' . $item['level'];
        $html .= '<a href="#' . esc_attr($item['id']) . '" class="' . esc_attr($class) . '">';
        $html .= esc_html($item['text']);
        $html .= '</a>';
    }

    $html .= '</aside>';
    return $html;
}

/**
 * Auto-add heading IDs to post content.
 */
add_filter('the_content', function ($content) {
    if (!is_singular() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $result = kv_generate_toc($content);
    return $result['content'];
}, 5);
