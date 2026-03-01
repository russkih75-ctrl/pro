<?php
/**
 * Single Post Template
 * Layout: TOC sidebar (left) + Article content (right)
 * Features: breadcrumbs, reading time, author box, FAQ, related posts
 *
 * @package Kvadratyra
 */

get_header();

// Get TOC data before rendering content
$raw_content = get_the_content();
$raw_content = apply_filters('the_content', $raw_content);

// Generate TOC
$toc_data = kv_generate_toc($raw_content);
$toc_items = $toc_data['items'];
$article_content = $toc_data['content'];

$default_calc_service = 'roof';
$cats_for_calc = get_the_category();
if ($cats_for_calc) {
    foreach ($cats_for_calc as $calc_cat) {
        $slug = isset($calc_cat->slug) ? (string) $calc_cat->slug : '';
        if (strpos($slug, 'fas') !== false) {
            $default_calc_service = 'facade';
            break;
        }
        if (strpos($slug, 'zabor') !== false) {
            $default_calc_service = 'fence';
            break;
        }
        if (strpos($slug, 'krov') !== false || strpos($slug, 'roof') !== false) {
            $default_calc_service = 'roof';
            break;
        }
    }
}
?>

<div class="container">
    <?php kv_breadcrumbs(); ?>
</div>

<div class="container">
    <div class="article-layout">
        <!-- TOC Sidebar -->
        <div class="article-layout__sidebar">
            <?php if (!empty($toc_items)) : ?>
                <div class="bento-card" style="position: sticky; top: 100px; padding: 24px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <?php echo kv_render_toc($toc_items); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Article Content -->
        <article class="article-layout__main bento-card" id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="padding: clamp(24px, 5vw, 48px); border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
            <?php while (have_posts()) : the_post(); ?>

                <!-- Article Header -->
                <header class="article-header">
                    <?php
                    $cats = get_the_category();
                    if ($cats) :
                        ?>
                        <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>" class="hero__badge" style="margin-bottom:12px;">
                            <?php echo esc_html($cats[0]->name); ?>
                        </a>
                    <?php endif; ?>

                    <h1 class="entry-title">
                        <?php the_title(); ?>
                    </h1>

                    <div class="article-meta">
                        <span class="article-meta__item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo get_the_date('d.m.Y'); ?>
                        </span>
                        <span class="article-meta__item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?php echo kv_reading_time(); ?>
                        </span>
                        <span class="article-meta__item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <?php the_author(); ?>
                        </span>
                    </div>

                    <?php if (has_post_thumbnail()) : ?>
                        <div class="article-hero-img">
                            <?php the_post_thumbnail('kv-hero', ['loading' => 'eager', 'style' => 'width:100%;height:auto;display:block;']); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- Article Body -->
                <div class="entry-content">
                    <?php echo $article_content; ?>
                </div>

                <!-- Intent continuation -->
                <section class="bento-card intent-continuation" style="background: var(--bg-alt); margin-top: 40px;">
                    <h2>Нужен расчёт под ваш город?</h2>
                    <p>Откройте GEO-страницу: покажем сроки доставки, ориентиры по цене, гарантии и удобный сценарий записи на просмотр образцов.</p>
                    <div class="btn-group">
                        <a class="btn btn--primary btn--sm" href="<?php echo esc_url(home_url('/geo/')); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            Выбрать город
                        </a>
                        <a class="btn btn--sm" href="<?php echo esc_url(home_url('/dostavka-oplata/')); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-2px;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg> Доставка и оплата</a>
                        <a class="btn btn--sm" href="<?php echo esc_url(home_url('/garantiya/')); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-2px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg> Гарантия</a>
                    </div>
                </section>

                <section class="calc-context bento-card calc-context-block" style="margin-top: 24px; padding: 32px;">
                    <h2>Рассчитайте именно ваш объект</h2>
                    <p>Подготовили расширенный калькулятор по теме этой статьи: ориентир по бюджету, срокам и 3 сценария реализации.</p>
                    <?php
                    get_template_part('template-parts/quiz-calculator', null, [
                        'context' => 'article',
                        'default_service' => $default_calc_service,
                    ]);
                    ?>
                    <div class="bento-grid mt-4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                        <div class="bento-card" style="padding: 24px; background: var(--bg-alt);">
                            <h3 class="card__title">Эконом</h3>
                            <div class="card__text">Минимальный вход по бюджету, базовые материалы, контрольные точки по качеству.</div>
                        </div>
                        <div class="bento-card" style="padding: 24px; background: var(--bg-alt); border-color: var(--accent);">
                            <h3 class="card__title" style="color: var(--accent);">Оптимум</h3>
                            <div class="card__text">Лучший баланс цена/ресурс/срок. Самый частый выбор для частных домов.</div>
                        </div>
                        <div class="bento-card" style="padding: 24px; background: var(--bg-alt);">
                            <h3 class="card__title">Премиум</h3>
                            <div class="card__text">Максимальный ресурс, расширенная гарантия и приоритетная логистика.</div>
                        </div>
                    </div>
                </section>

                <!-- Tags -->
                <?php
                $tags = get_the_tags();
                if ($tags) :
                    ?>
                    <div class="tags-list">
                        <?php foreach ($tags as $tag) : ?>
                            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="btn btn--sm">
                                #<?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Author Box -->
                <div class="author-box bento-card" style="background: var(--bg-alt); margin-top: 40px; padding: 24px;">
                    <div class="author-box__avatar">
                        <?php echo mb_substr(get_the_author(), 0, 1); ?>
                    </div>
                    <div>
                        <div class="author-box__name"><?php the_author(); ?></div>
                        <div class="author-box__bio">
                            <?php echo esc_html(get_the_author_meta('description') ?: 'Андрей, инженер. Опыт 20 лет.'); ?>
                            <span style="display:block;margin-top:10px;">
                                <a class="btn btn--sm" href="tel:<?php echo esc_attr(kv_phone(false)); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-2px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg> Позвонить: <?php echo esc_html(kv_phone(true)); ?></a>
                            </span>
                            <span style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                                <?php $maxChat = get_option('kv_max_chat_url', defined('KV_DEFAULT_MAX_CHAT') ? KV_DEFAULT_MAX_CHAT : ''); ?>
                                <?php if ($maxChat) : ?>
                                    <a class="btn btn--sm" href="<?php echo esc_url($maxChat); ?>" target="_blank" rel="noopener"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-2px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg> Чат в MAX</a>
                                <?php endif; ?>
                                <?php $okGroup = get_option('kv_ok_group_url', defined('KV_DEFAULT_OK_GROUP') ? KV_DEFAULT_OK_GROUP : ''); ?>
                                <?php if ($okGroup) : ?>
                                    <a class="btn btn--sm" href="<?php echo esc_url($okGroup); ?>" target="_blank" rel="noopener"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-2px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg> Канал в ОК</a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- In-article FAQ: only on front page include (single posts rely on post content for FAQ) -->

            <?php endwhile; ?>

            <!-- Related Posts -->
            <?php
            $related = new WP_Query([
                'posts_per_page' => 3,
                'post__not_in'   => [get_the_ID()],
                'category__in'   => wp_get_post_categories(get_the_ID()),
                'post_status'    => 'publish',
                'orderby'        => 'rand',
            ]);

            if ($related->have_posts()) :
                ?>
                <div class="mt-4">
                    <h2 class="section__title" style="font-size:24px;">Читайте также</h2>
                    <div class="bento-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                        <?php while ($related->have_posts()) : $related->the_post(); ?>
                            <a href="<?php the_permalink(); ?>" class="bento-card card--link blog-card" style="padding: 0;">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img class="blog-card__img lazyload" data-src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'kv-thumb')); ?>" alt="<?php the_title_attribute(); ?>" width="300" height="200">
                                <?php endif; ?>
                                <div class="blog-card__body" style="padding: 20px;">
                                    <div class="blog-card__meta">
                                        <span><?php echo get_the_date('d.m.Y'); ?></span>
                                    </div>
                                    <div class="blog-card__title"><?php the_title(); ?></div>
                                </div>
                            </a>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; ?>

        </article>

        <!-- Next Action Block -->
        <section class="next-action bento-card animate-in" style="grid-column: 1 / -1; margin-top: 40px; background: var(--bg-alt);">
            <h2>Готовы приступить к работе?</h2>
            <p>
                Оставьте заявку на бесплатный замер или задайте вопрос нашему инженеру напрямую в мессенджере.
            </p>
            <div class="btn-group">
                <a href="#calculator" class="btn btn--primary btn--lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-4px;"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="14.01"></line><line x1="12" y1="14" x2="12" y2="14.01"></line><line x1="8" y1="14" x2="8" y2="14.01"></line><line x1="16" y1="18" x2="16" y2="18.01"></line><line x1="12" y1="18" x2="12" y2="18.01"></line><line x1="8" y1="18" x2="8" y2="18.01"></line></svg>
                    Перейти к расчёту
                </a>
                <a href="<?php echo esc_url(get_option('kv_max_chat_url', defined('KV_DEFAULT_MAX_CHAT') ? KV_DEFAULT_MAX_CHAT : '#')); ?>" class="btn btn--outline btn--lg" target="_blank" rel="noopener">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-4px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Спросить инженера
                </a>
            </div>
        </section>
    </div>
</div>

<?php get_footer(); ?>
