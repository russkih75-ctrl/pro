<?php
/**
 * Search results template (clean, noindex handled in functions.php).
 *
 * @package Kvadratyra
 */
get_header();
?>

<div class="container" style="padding:24px 0;">
  <?php kv_breadcrumbs(); ?>

  <header style="margin:10px 0 18px;">
    <h1 style="font-size:32px;font-weight:900;letter-spacing:-0.02em;margin:0 0 10px;line-height:1.15;">
      Поиск: <?php echo esc_html(get_search_query()); ?>
    </h1>
    <p class="text-muted">Результаты поиска по сайту.</p>
  </header>

  <?php if (have_posts()) : ?>
    <div class="grid grid--3">
      <?php while (have_posts()) : the_post(); ?>
        <a href="<?php the_permalink(); ?>" class="card card--link blog-card">
          <?php if (has_post_thumbnail()) : ?>
            <img class="blog-card__img lazyload" data-src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'kv-card')); ?>" alt="<?php the_title_attribute(); ?>" width="600" height="400">
          <?php endif; ?>
          <div class="blog-card__body">
            <div class="blog-card__meta">
              <span><?php echo esc_html(get_post_type()); ?></span>
              <span><?php echo get_the_date('d.m.Y'); ?></span>
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
      <p class="text-muted">Ничего не найдено. Попробуйте другой запрос.</p>
    </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>

