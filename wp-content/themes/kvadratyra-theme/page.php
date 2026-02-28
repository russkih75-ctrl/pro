<?php get_header(); ?>
<div class="container" style="padding:18px 0">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article class="card">
      <h1 style="margin:0 0 10px"><?php the_title(); ?></h1>
      <div class="entry-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>

