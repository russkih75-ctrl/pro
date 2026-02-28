<?php
/**
 * Header template
 *
 * @package Kvadratyra
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b10">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://mc.yandex.ru">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#site-main" class="skip-link">Перейти к содержимому</a>

<header class="site-header" id="site-header">
    <div class="container">
        <div class="bar">
            <!-- Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo" aria-label="<?php bloginfo('name'); ?>">
                <?php if (has_custom_logo()) : ?>
                    <?php
                    $logo_id = get_theme_mod('custom_logo');
                    $logo_url = wp_get_attachment_image_url($logo_id, 'full');
                    ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" width="140" height="32">
                <?php else : ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3 21V9l9-7 9 7v12H3z" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path d="M9 21V13h6v8" stroke="currentColor" stroke-width="2" fill="none"/>
                    </svg>
                    <span><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </a>

            <!-- Navigation -->
            <nav class="nav" id="kv-nav" aria-label="Главная навигация">
                <?php
                if (has_nav_menu('primary')) {
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'walker'         => new KV_Nav_Walker(),
                        'depth'          => 1,
                    ]);
                } else {
                    // Fallback menu
                    ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                    <a href="<?php echo esc_url(home_url('/geo/')); ?>">География</a>
                    <a href="<?php echo esc_url(home_url('/krovlya/')); ?>">Кровля</a>
                    <a href="<?php echo esc_url(home_url('/fasady/')); ?>">Фасады</a>
                    <a href="<?php echo esc_url(home_url('/zabory/')); ?>">Заборы</a>
                    <a href="<?php echo esc_url(home_url('/guides/')); ?>">Гайды</a>
                    <a href="<?php echo esc_url(home_url('/blog/')); ?>">Блог</a>
                    <?php
                }
                ?>
            </nav>

            <!-- CTA + Burger -->
            <div class="site-header__actions">
                <?php $tg_header = get_option('kv_telegram', ''); ?>
                <?php if ($tg_header) : ?>
                    <a href="<?php echo esc_url($tg_header); ?>" class="btn btn--sm kv-header-tg" target="_blank" rel="noopener" aria-label="Telegram">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:-2px;"><path d="M20.665 3.717l-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l.002.001-.314 4.692c.46 0 .663-.211.921-.46l2.211-2.15 4.599 3.397c.848.467 1.457.227 1.668-.785l3.019-14.228c.309-1.239-.473-1.8-1.282-1.434z"/></svg>
                    </a>
                <?php endif; ?>
                <a href="tel:<?php echo esc_attr(kv_phone(false)); ?>" class="btn btn--primary btn--sm kv-header-cta">
                    Звонок
                </a>

                <button class="kv-mobile-toggle" id="kv-burger" aria-label="Меню" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </div>
</header>

<div class="reading-progress" aria-hidden="true"></div>

<!-- Scroll depth gamification badge -->
<div class="scroll-badge" aria-hidden="true">
    <span class="scroll-badge__icon"></span>
    <span class="scroll-badge__text"></span>
</div>

<!-- Time on page engagement -->
<div class="time-badge" aria-hidden="true">
    <span class="time-badge__dot"></span>
    <span class="time-badge__text"></span>
</div>

<main id="site-main">
