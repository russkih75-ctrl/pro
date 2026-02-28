<?php
/**
 * FAQ Accordion Block
 * Used on front-page and in single posts.
 * Also outputs FAQPage Schema.org JSON-LD.
 *
 * @package Kvadratyra
 */

if (!defined('ABSPATH')) exit;

// FAQ data — construction-focused questions
$kv_faqs = [
    [
        'q' => 'Сколько стоит монтаж кровли под ключ?',
        'a' => 'Стоимость зависит от площади крыши, выбранного материала и сложности конструкции. Средняя цена — от 1 500 руб/м² за профнастил до 3 500 руб/м² за мягкую черепицу с утеплением. Точную стоимость назовём после бесплатного замера.',
    ],
    [
        'q' => 'Какой материал лучше для кровли частного дома?',
        'a' => 'Для частного дома чаще всего выбирают металлочерепицу (долговечность + красивый вид), профнастил (экономичность), мягкую черепицу (подходит для сложных форм крыш) или фальцевую кровлю (максимальная герметичность). Поможем подобрать оптимальный вариант под ваш бюджет и задачу.',
    ],
    [
        'q' => 'Сколько времени занимает монтаж?',
        'a' => 'Забор из профнастила — 1-2 дня. Кровля дома до 150 м² — 3-5 рабочих дней. Отделка фасада — 5-7 дней. Точные сроки зависят от объёма и погодных условий. Работаем без простоев.',
    ],
    [
        'q' => 'Даёте ли вы гарантию на работы?',
        'a' => 'Да, предоставляем письменную гарантию до 10 лет на монтажные работы и до 50 лет на материалы (зависит от производителя). Гарантия фиксируется в договоре. Если обнаружите проблему в гарантийный период — устраним бесплатно.',
    ],
    [
        'q' => 'Работаете ли вы зимой?',
        'a' => 'Монтаж кровли и заборов выполняем круглый год. Фасадные работы (мокрый фасад) — при температуре выше +5°C. Вентилируемые фасады и сайдинг можно монтировать зимой. Каждый случай рассматриваем индивидуально.',
    ],
    [
        'q' => 'Как заказать бесплатный замер?',
        'a' => 'Позвоните нам, напишите в Telegram или оставьте заявку на сайте. Инженер перезвонит в течение 15 минут, согласует удобное время и приедет на объект в течение 24 часов. Замер, консультация и расчёт — бесплатно.',
    ],
    [
        'q' => 'Какой забор лучше: профнастил или евроштакетник?',
        'a' => 'Профнастил — полностью закрытый забор, защищает от ветра и шума, дешевле. Евроштакетник — эстетичный внешний вид, пропускает свет и воздух, дороже на 20-30%. Для дачи чаще выбирают евроштакетник, для частного дома — профнастил.',
    ],
    [
        'q' => 'Чем лучше утеплить фасад дома?',
        'a' => 'Основные варианты: минеральная вата (негорючий, паропроницаемый, от 200 руб/м²), пеноплекс/ЭППС (отличная теплоизоляция, влагостойкий, от 180 руб/м²), ППУ-напыление (бесшовное утепление, от 350 руб/м²). Выбор зависит от материала стен, климата и бюджета.',
    ],
];

// Only output if we have FAQs
if (empty($kv_faqs)) return;
?>

<div class="faq" itemscope itemtype="https://schema.org/FAQPage">
    <?php foreach ($kv_faqs as $i => $faq) : ?>
        <div class="faq-item animate-in" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <div class="faq-q faq-question" role="button" tabindex="0" aria-expanded="false" data-faq-toggle>
                <span itemprop="name"><?php echo esc_html($faq['q']); ?></span>
                <span class="faq-q__icon">+</span>
            </div>
            <div class="faq-a faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                <div itemprop="text"><?php echo esc_html($faq['a']); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
// Output FAQPage Schema JSON-LD (only once on the page)
if (!defined('KV_FAQ_SCHEMA_DONE')) {
    define('KV_FAQ_SCHEMA_DONE', true);

    $schema_faq = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => [],
    ];

    foreach ($kv_faqs as $faq) {
        $schema_faq['mainEntity'][] = [
            '@type' => 'Question',
            'name'  => $faq['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $faq['a'],
            ],
        ];
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema_faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
?>
