<?php
/**
 * llms.txt & llms-full.txt — Markdown files for LLM/AI bots.
 * Standard: https://llmstxt.org/
 *
 * /llms.txt      — compact site map in Markdown
 * /llms-full.txt — extended version with page excerpts
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    add_rewrite_rule('^llms\.txt$', 'index.php?kv_llms=short', 'top');
    add_rewrite_rule('^llms-full\.txt$', 'index.php?kv_llms=full', 'top');
}, 21);

add_filter('query_vars', function ($vars) {
    $vars[] = 'kv_llms';
    return $vars;
});

add_action('template_redirect', function () {
    $mode = get_query_var('kv_llms');
    if (!$mode) return;

    status_header(200);
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Robots-Tag: noindex');
    header('Cache-Control: public, max-age=86400');

    $site  = home_url('/');
    $name  = get_bloginfo('name') ?: 'Квадратура';
    $desc  = get_bloginfo('description') ?: 'Монтаж кровли, фасадов и заборов под ключ';
    $phone = function_exists('kv_phone') ? kv_phone() : get_option('kv_phone', '');
    $email = get_option('kv_email', '');
    $tg    = get_option('kv_telegram', '');

    $md = "# {$name}\n\n";
    $md .= "> {$desc}\n\n";
    $md .= "Строительная компания. Монтаж кровли, отделка фасадов, установка заборов под ключ.\n";
    $md .= "Работаем по Воронежской, Тамбовской, Саратовской и Волгоградской областям.\n";
    $md .= "Гарантия до 10 лет. Собственные бригады. Материалы от производителей.\n\n";

    // Services
    $md .= "## Услуги\n\n";
    $md .= "- [Монтаж и ремонт кровли]({$site}krovlya/): металлочерепица, профнастил, мягкая кровля, фальцевая кровля\n";
    $md .= "- [Отделка и утепление фасадов]({$site}fasady/): сайдинг, фасадные панели, штукатурка, вентфасад\n";
    $md .= "- [Установка заборов]({$site}zabory/): профнастил, евроштакетник, 3D-сетка, сетка-рабица\n\n";

    // Calculators
    $md .= "## Калькуляторы\n\n";
    $md .= "- [Заказать монтаж — онлайн-калькулятор]({$site}#calculator): multi-step форма расчёта стоимости\n";
    $md .= "- [Расчёт материалов]({$site}#calculator): кровля, фасад, забор — спецификация с запасом 10%\n\n";

    // Geography
    $md .= "## География\n\n";
    $md .= "- [Все города]({$site}geo/)\n";
    $cities = ['Борисоглебск', 'Тамбов', 'Мичуринск', 'Балашов', 'Камышин', 'Урюпинск'];
    foreach ($cities as $c) {
        $slug = sanitize_title($c);
        $md .= "- [{$c}]({$site}geo/{$slug}/)\n";
    }
    $md .= "\n";

    // Contacts
    $md .= "## Контакты\n\n";
    if ($phone) $md .= "- Телефон: {$phone}\n";
    if ($email) $md .= "- Email: {$email}\n";
    if ($tg)    $md .= "- Telegram: {$tg}\n";
    $md .= "\n";

    if ($mode === 'full') {
        // Blog posts
        $posts = get_posts([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'numberposts'    => 50,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        if ($posts) {
            $md .= "## Блог\n\n";
            foreach ($posts as $p) {
                $url     = get_permalink($p);
                $title   = get_the_title($p);
                $excerpt = wp_strip_all_tags(get_the_excerpt($p));
                $excerpt = wp_trim_words($excerpt, 40, '...');
                $md .= "### [{$title}]({$url})\n\n{$excerpt}\n\n";
            }
        }

        // Guides
        $guides = get_posts([
            'post_type'      => 'guide',
            'post_status'    => 'publish',
            'numberposts'    => 50,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        if ($guides) {
            $md .= "## Гайды и чек-листы\n\n";
            foreach ($guides as $g) {
                $url     = get_permalink($g);
                $title   = get_the_title($g);
                $excerpt = wp_strip_all_tags(get_the_excerpt($g));
                $excerpt = wp_trim_words($excerpt, 40, '...');
                $md .= "### [{$title}]({$url})\n\n{$excerpt}\n\n";
            }
        }

        // Pages
        $pages = get_pages(['post_status' => 'publish', 'sort_column' => 'menu_order']);
        if ($pages) {
            $md .= "## Страницы\n\n";
            foreach ($pages as $pg) {
                $url   = get_permalink($pg);
                $title = get_the_title($pg);
                $md .= "- [{$title}]({$url})\n";
            }
            $md .= "\n";
        }
    }

    echo $md;
    exit;
}, 0);
