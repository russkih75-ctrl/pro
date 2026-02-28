<?php
/**
 * Fallback template (required by WordPress theme checker / installer).
 */
get_header();
?>

<div class="container" style="padding:18px 0">
  <div class="card">
    <?php if (have_posts()): ?>
      <?php while (have_posts()): the_post(); ?>
        <article style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,.12)">
          <h2 style="margin:0 0 6px">
            <a href="<?php the_permalink(); ?>" style="text-decoration:none"><?php the_title(); ?></a>
          </h2>
          <div style="color:#9fb0c3">
            <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 28)); ?>
          </div>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <div style="color:#9fb0c3">Пока нет записей.</div>
    <?php endif; ?>
  </div>
</div>

<?php get_footer(); ?>

