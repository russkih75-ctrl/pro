<?php get_header(); ?>
<div class="container" style="padding:40px 0">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article class="bento-card" style="padding: 48px;">
      <h1 class="entry-title" style="margin:0 0 24px"><?php the_title(); ?></h1>
      <div class="entry-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>


