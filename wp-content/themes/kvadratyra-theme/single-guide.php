<?php
/**
 * Single Guide template (Answer-first + TOC + CTA).
 *
 * @package Kvadratyra
 */
get_header();

// Prepare TOC with heading anchors
$raw_content = get_the_content();
$raw_content = apply_filters('the_content', $raw_content);
$toc_data = function_exists('kv_generate_toc') ? kv_generate_toc($raw_content) : ['items' => [], 'content' => $raw_content];
$toc_items = $toc_data['items'] ?? [];
$content   = $toc_data['content'] ?? $raw_content;

$excerpt = trim((string) get_the_excerpt());
?>

<div class="container">
  <?php kv_breadcrumbs(); ?>
</div>

<div class="container">
  <div class="article-layout">
    <div class="article-layout__sidebar">
      <?php if (!empty($toc_items) && function_exists('kv_render_toc')) : ?>
        <?php echo kv_render_toc($toc_items); ?>
      <?php endif; ?>
    </div>

    <article class="article-layout__main" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <?php while (have_posts()) : the_post(); ?>
        <header class="article-header">
          <?php
          $topics = get_the_terms(get_the_ID(), 'guide_topic');
          if ($topics && !is_wp_error($topics)) :
            $t = $topics[0];
          ?>
            <a href="<?php echo esc_url(get_term_link($t)); ?>" class="hero__badge" style="margin-bottom:12px;">
              <?php echo esc_html($t->name); ?>
            </a>
          <?php endif; ?>

          <h1 class="entry-title" style="font-size:36px;font-weight:900;letter-spacing:-0.02em;margin-bottom:16px;line-height:1.2;">
            <?php the_title(); ?>
          </h1>

          <div class="article-meta">
            <span class="article-meta__item"><?php echo get_the_date('d.m.Y'); ?></span>
            <span class="article-meta__item"><?php echo kv_reading_time(); ?></span>
            <span class="article-meta__item"><?php the_author(); ?></span>
          </div>

          <?php if ($excerpt) : ?>
            <div class="card" style="margin-top:16px;">
              <div class="card__title">Короткий ответ</div>
              <div class="card__text" style="font-size:16px;line-height:1.8;color:var(--text-secondary);"><?php echo esc_html($excerpt); ?></div>
            </div>
          <?php endif; ?>

          <?php if (has_post_thumbnail()) : ?>
            <div style="margin:20px 0;border-radius:var(--radius);overflow:hidden;">
              <?php the_post_thumbnail('kv-hero', ['loading' => 'eager', 'style' => 'width:100%;height:auto;display:block;']); ?>
            </div>
          <?php endif; ?>
        </header>

        <div class="entry-content">
          <?php echo $content; ?>
        </div>

        <section style="margin-top:28px;">
          <div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
              <div class="card__title">Нужна смета под ваш объект?</div>
              <div class="card__text">Ответим за 15 минут. Замер — бесплатно. Цена фиксируется в договоре.</div>
            </div>
            <div class="btn-group">
              <a href="<?php echo esc_url(home_url('/#quiz')); ?>" class="btn btn--primary">Рассчитать стоимость</a>
              <a href="tel:<?php echo esc_attr(kv_phone(false)); ?>" class="btn">Позвонить</a>
              <?php $maxChat = get_option('kv_max_chat_url', defined('KV_DEFAULT_MAX_CHAT') ? KV_DEFAULT_MAX_CHAT : ''); ?>
              <?php if ($maxChat) : ?>
                <a href="<?php echo esc_url($maxChat); ?>" class="btn" target="_blank" rel="noopener">Чат в MAX</a>
              <?php endif; ?>
              <?php $okGroup = get_option('kv_ok_group_url', defined('KV_DEFAULT_OK_GROUP') ? KV_DEFAULT_OK_GROUP : ''); ?>
              <?php if ($okGroup) : ?>
                <a href="<?php echo esc_url($okGroup); ?>" class="btn" target="_blank" rel="noopener">Канал в ОК</a>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <?php get_template_part('template-parts/faq-block'); ?>
      <?php endwhile; ?>
    </article>
  </div>
</div>

<?php get_footer(); ?>

