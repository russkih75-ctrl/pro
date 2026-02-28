<?php
/**
 * Guides taxonomy: guide_topic
 *
 * @package Kvadratyra
 */
get_header();

$term = get_queried_object();
$title = is_object($term) && !empty($term->name) ? $term->name : 'Тема';
?>

<div class="container" style="padding:24px 0;">
  <?php kv_breadcrumbs(); ?>

  <header style="margin:10px 0 18px;">
    <h1 style="font-size:36px;font-weight:900;letter-spacing:-0.02em;margin:0 0 10px;line-height:1.15;">Гайды: <?php echo esc_html($title); ?></h1>
    <p class="text-muted" style="max-width:900px;">Подборка гайдов по теме. Каждый материал начинается с короткого ответа и даёт понятный план действий.</p>
  </header>

  <div style="margin-bottom:16px;">
    <a class="btn btn--sm" href="<?php echo esc_url(get_post_type_archive_link('guide')); ?>">← Все гайды</a>
  </div>

  <?php if (have_posts()) : ?>
    <div class="grid grid--3">
      <?php while (have_posts()) : the_post(); ?>
        <a href="<?php the_permalink(); ?>" class="card card--link blog-card">
          <?php if (has_post_thumbnail()) : ?>
            <img class="blog-card__img lazyload" data-src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'kv-card')); ?>" alt="<?php the_title_attribute(); ?>" width="600" height="400">
          <?php endif; ?>
          <div class="blog-card__body">
            <div class="blog-card__meta">
              <span><?php echo get_the_date('d.m.Y'); ?></span>
              <span><?php echo kv_reading_time(); ?></span>
            </div>
            <div class="blog-card__title"><?php the_title(); ?></div>
            <div class="blog-card__excerpt"><?php echo wp_trim_words(get_the_excerpt(), 18, '...'); ?></div>
          </div>
        </a>
      <?php endwhile; ?>
    </div>

    <div style="margin-top:28px;">
      <?php the_posts_pagination(['mid_size' => 1]); ?>
    </div>
  <?php else : ?>
    <div class="card" style="padding:28px;">
      <p class="text-muted">В этой теме пока нет гайдов.</p>
    </div>
  <?php endif; ?>

  <section class="section" style="padding:40px 0 0;">
    <div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
      <div>
        <div class="card__title">Нужен расчёт под ваш объект?</div>
        <div class="card__text">Ответим за 15 минут и сделаем предварительную смету.</div>
      </div>
      <div class="btn-group">
        <a href="<?php echo esc_url(home_url('/#quiz')); ?>" class="btn btn--primary btn--lg">Рассчитать стоимость</a>
        <a href="tel:<?php echo esc_attr(kv_phone(false)); ?>" class="btn btn--lg">Позвонить</a>
      </div>
    </div>
  </section>
</div>

<?php get_footer(); ?>

