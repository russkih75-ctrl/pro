<?php
/**
 * GEO hub template: /geo/
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;

get_header();

$regions = function_exists('kv_geo_get_regions') ? kv_geo_get_regions() : [];
$services = function_exists('kv_geo_get_services') ? kv_geo_get_services() : [];
?>

<div class="container" style="padding:24px 0;">
  <?php if (function_exists('kv_breadcrumbs')) kv_breadcrumbs(); ?>

  <section class="section" style="padding:10px 0 18px;">
    <h1 style="font-size:36px;font-weight:900;letter-spacing:-0.02em;margin:0 0 10px;line-height:1.15;">Регионы и города работ</h1>
    <p class="section__subtitle" style="max-width:980px;">Выберите город, чтобы увидеть цены, материалы и расчёт по кровле, фасадам и заборам. Страницы построены по интентам (ремонт, материалы, цена, монтаж, под ключ, калькулятор) — удобно сравнивать и принимать решение.</p>

    <div class="btn-group" style="margin-top:14px;">
      <a href="<?php echo esc_url(home_url('/#quiz')); ?>" class="btn btn--primary btn--lg">Рассчитать стоимость</a>
      <a href="tel:<?php echo esc_attr(function_exists('kv_phone') ? kv_phone(false) : ''); ?>" class="btn btn--lg">Позвонить</a>
    </div>
  </section>

  <?php if (!empty($services)) : ?>
    <section class="section" style="padding:0 0 22px;">
      <div class="card" style="padding:18px 18px 16px;">
        <div class="card__title">Услуги</div>
        <div class="regions-grid" style="margin-top:12px;">
          <?php foreach ($services as $s) : if (!is_array($s)) continue; ?>
            <?php $svc_slug = sanitize_title($s['slug'] ?? ''); ?>
            <a href="<?php echo esc_url($svc_slug ? home_url('/' . $svc_slug . '/') : home_url('/#services')); ?>"><?php echo esc_html($s['name'] ?? ''); ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="section" style="padding:0;">
    <h2 class="section__title" style="margin-bottom:14px;">Выберите область</h2>
    <div class="grid grid--2">
      <?php foreach ($regions as $r) : if (!is_array($r)) continue; ?>
        <?php
        $slug = sanitize_title($r['slug'] ?? '');
        $cities = function_exists('kv_geo_get_cities_by_region') ? kv_geo_get_cities_by_region($slug) : [];
        // sort by population desc
        usort($cities, function ($a, $b) {
          return (int)($b['population'] ?? 0) <=> (int)($a['population'] ?? 0);
        });
        $top = array_slice($cities, 0, 10);
        ?>
        <div class="card" style="padding:18px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
              <div class="card__title"><?php echo esc_html($r['name'] ?? ''); ?></div>
              <div class="text-muted" style="margin-top:6px;"><?php echo count($cities); ?> город(ов) и районов</div>
            </div>
            <a class="btn btn--sm" href="<?php echo esc_url(home_url('/#quiz')); ?>">Смета за 30 сек</a>
          </div>

          <?php if (!empty($top)) : ?>
            <div class="regions-grid" style="margin-top:12px;">
              <?php foreach ($top as $c) : if (!is_array($c)) continue; ?>
                <?php $cSlug = sanitize_title($c['slug'] ?? ''); if (!$cSlug) continue; ?>
                <a href="<?php echo esc_url(home_url('/geo/' . $cSlug . '/')); ?>"><?php echo esc_html($c['name'] ?? ''); ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section" style="padding:34px 0 0;">
    <div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
      <div>
        <div class="card__title">Не нашли свой город?</div>
        <div class="card__text">Напишите в Telegram — добавим город и дадим расчёт.</div>
      </div>
      <div class="btn-group">
        <?php $tg = get_option('kv_telegram', ''); ?>
        <?php if ($tg) : ?>
          <a href="<?php echo esc_url($tg); ?>" class="btn btn--primary">Написать в Telegram</a>
        <?php endif; ?>
        <a href="tel:<?php echo esc_attr(function_exists('kv_phone') ? kv_phone(false) : ''); ?>" class="btn">Позвонить</a>
      </div>
    </div>
  </section>
</div>

<?php get_footer(); ?>

