<?php
/**
 * Page Template: Blog (/blog/)
 * Renders a posts archive on a regular WP Page with slug "blog".
 *
 * @package Kvadratyra
 */

if (!defined('ABSPATH')) exit;

get_header();

$paged = max(1, (int) get_query_var('paged'));
$q = new WP_Query([
  'post_type'      => 'post',
  'post_status'    => 'publish',
  'posts_per_page' => 9,
  'paged'          => $paged,
]);
?>

<div class="container">
  <?php if (function_exists('kv_breadcrumbs')) kv_breadcrumbs(); ?>
</div>

<header class="page-header section">
  <div class="container">
    <h1 class="page-title"><?php echo esc_html(get_the_title()); ?></h1>
    <p class="section__subtitle">Экспертные статьи про кровлю, фасады и заборы: материалы, цены, ошибки и чек‑листы.</p>
    <div class="btn-group">
      <a href="<?php echo esc_url(home_url('/#calculator')); ?>" class="btn btn--primary">Рассчитать стоимость</a>
      <a href="tel:<?php echo esc_attr(function_exists('kv_phone') ? kv_phone(false) : ''); ?>" class="btn">Позвонить</a>
    </div>
  </div>
</header>

<div class="container section">
  <div class="grid grid--3">
    <?php if ($q->have_posts()) : ?>
      <?php while ($q->have_posts()) : $q->the_post(); ?>
        <a href="<?php the_permalink(); ?>" class="card card--link blog-card">
          <?php if (has_post_thumbnail()) : ?>
            <img class="blog-card__img lazyload" data-src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'kv-card')); ?>" alt="<?php the_title_attribute(); ?>" width="600" height="400">
          <?php endif; ?>
          <div class="blog-card__body">
            <div class="blog-card__meta">
              <span><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
              <?php if (function_exists('kv_reading_time')) : ?>
                <span><?php echo esc_html(kv_reading_time()); ?></span>
              <?php endif; ?>
            </div>
            <div class="blog-card__title"><?php the_title(); ?></div>
            <div class="blog-card__excerpt"><?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 18, '...')); ?></div>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    <?php else : ?>
      <div class="card" style="grid-column:1/-1;text-align:center;padding:40px;">
        <p class="text-muted">Пока нет статей.</p>
      </div>
    <?php endif; ?>
  </div>

  <?php
  // Pagination
  $big = 999999999;
  $links = paginate_links([
    'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
    'format'    => '?paged=%#%',
    'current'   => $paged,
    'total'     => (int) $q->max_num_pages,
    'type'      => 'list',
    'prev_text' => '←',
    'next_text' => '→',
  ]);

  if ($links) {
    echo '<div style="margin-top:24px;">' . wp_kses_post($links) . '</div>';
  }
  ?>
</div>

<?php get_footer(); ?>

