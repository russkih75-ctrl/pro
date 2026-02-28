<?php
/**
 * CPT: Guides (контент-хаб как у mayai, но под строй-тематику).
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    // Post type: guide
    register_post_type('guide', [
        'labels' => [
            'name'               => 'Гайды',
            'singular_name'      => 'Гайд',
            'add_new'            => 'Добавить гайд',
            'add_new_item'       => 'Добавить гайд',
            'edit_item'          => 'Редактировать гайд',
            'new_item'           => 'Новый гайд',
            'view_item'          => 'Просмотр гайда',
            'search_items'       => 'Искать гайды',
            'not_found'          => 'Гайды не найдены',
            'not_found_in_trash' => 'В корзине гайдов нет',
            'menu_name'          => 'Гайды',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'guides', 'with_front' => false],
        'menu_icon'    => 'dashicons-welcome-learn-more',
        'supports'     => ['title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions'],
        'show_in_rest' => true,
    ]);

    // Taxonomy: guide_topic
    register_taxonomy('guide_topic', ['guide'], [
        'labels' => [
            'name'          => 'Темы гайдов',
            'singular_name' => 'Тема',
            'search_items'  => 'Искать темы',
            'all_items'     => 'Все темы',
            'edit_item'     => 'Редактировать тему',
            'update_item'   => 'Обновить тему',
            'add_new_item'  => 'Добавить тему',
            'new_item_name' => 'Название темы',
            'menu_name'     => 'Темы',
        ],
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'guides/topic', 'with_front' => false],
        'show_in_rest' => true,
    ]);
}, 0);

/**
 * One-shot rewrite flush after adding CPT (avoid manual permalinks click).
 */
add_action('after_switch_theme', function () {
    // Flush once after theme activation/switch
    flush_rewrite_rules(false);
    update_option('kv_guides_rewrite_flushed', '1', false);
});

