<?php
/**
 * Reviews Slider
 * Custom JS slider with dots navigation and prev/next buttons.
 *
 * @package Kvadratyra
 */

if (!defined('ABSPATH')) exit;

$reviews = [
    [
        'name'   => 'Алексей К.',
        'role'   => 'Борисоглебск, кровля',
        'text'   => 'Заменили старый шифер на металлочерепицу Grand Line. Бригада приехала вовремя, работали аккуратно, весь мусор убрали. Кровля выглядит отлично, уже прошёл сильный дождь — нигде не протекает. Рекомендую!',
        'stars'  => 5,
    ],
    [
        'name'   => 'Марина В.',
        'role'   => 'Анна, фасад',
        'text'   => 'Утеплили и обшили дом сайдингом за 5 дней. Сразу почувствовали разницу — зимой в доме стало заметно теплее. Мастера вежливые, делали всё по технологии. Цена оказалась даже ниже, чем у конкурентов.',
        'stars'  => 5,
    ],
    [
        'name'   => 'Сергей Д.',
        'role'   => 'Тамбов, забор',
        'text'   => 'Поставили забор из евроштакетника 85 метров с откатными воротами. Всё ровно, красиво, ворота работают отлично. Гарантию дали на 5 лет. Соседи уже тоже заказали у них.',
        'stars'  => 5,
    ],
    [
        'name'   => 'Елена М.',
        'role'   => 'Балашов, кровля + фасад',
        'text'   => 'Делали полный комплекс: кровля + утепление + фасад. Работа заняла 2 недели, но результат стоит каждого дня. Дом как новый! Отдельное спасибо за терпеливые консультации по выбору материалов.',
        'stars'  => 5,
    ],
    [
        'name'   => 'Игорь Т.',
        'role'   => 'Михайловка, забор',
        'text'   => 'Забор из профнастила 60 метров сделали за 2 дня. Столбы залиты бетоном, лаги приварены ровно, листы без царапин. Всё по уму. Буду обращаться ещё за навесом.',
        'stars'  => 5,
    ],
    [
        'name'   => 'Ольга Н.',
        'role'   => 'Мичуринск, кровля',
        'text'   => 'Ремонт мягкой кровли на гараже. Течь была в нескольких местах. Ребята нашли все проблемные участки, заменили повреждённые листы, промазали стыки. Уже полгода — сухо. Спасибо!',
        'stars'  => 4,
    ],
];
?>

<div class="reviews-slider" data-slider>
    <div class="reviews-slider__header">
        <div>
            <h2 class="section__title mb-0">Отзывы клиентов</h2>
            <p class="text-muted mt-1"><?php echo count($reviews); ?> реальных отзывов от наших заказчиков</p>
        </div>
        <div class="reviews-slider__controls">
            <button class="reviews-slider__btn" data-slider-prev aria-label="Предыдущий отзыв">←</button>
            <button class="reviews-slider__btn" data-slider-next aria-label="Следующий отзыв">→</button>
        </div>
    </div>

    <div class="reviews-slider__track" data-slider-track>
        <?php foreach ($reviews as $i => $review) : ?>
            <div class="review-card card" data-slide="<?php echo $i; ?>">
                <div class="review-card__stars">
                    <?php echo str_repeat('★', $review['stars']) . str_repeat('☆', 5 - $review['stars']); ?>
                </div>
                <div class="review-card__text">"<?php echo esc_html($review['text']); ?>"</div>
                <div class="review-card__author">
                    <div class="review-card__avatar"><?php echo mb_substr($review['name'], 0, 1); ?></div>
                    <div>
                        <div class="review-card__name"><?php echo esc_html($review['name']); ?></div>
                        <div class="review-card__role"><?php echo esc_html($review['role']); ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="reviews-slider__dots" data-slider-dots>
        <?php foreach ($reviews as $i => $review) : ?>
            <button class="reviews-slider__dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-dot="<?php echo $i; ?>" aria-label="Отзыв <?php echo $i + 1; ?>"></button>
        <?php endforeach; ?>
    </div>
</div>

<?php
// AggregateRating Schema
$avg = array_sum(array_column($reviews, 'stars')) / count($reviews);
$org_id = home_url('/') . '#organization';
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'HomeAndConstructionBusiness',
    '@id'      => $org_id,
    'name'     => get_bloginfo('name'),
    'url'      => home_url('/'),
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => round($avg, 1),
        'reviewCount' => count($reviews),
        'bestRating'  => 5,
        'worstRating' => 1,
    ],
];
echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
?>
