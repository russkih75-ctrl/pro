<?php
/**
 * Schema.org @graph for homepage
 * Types: HomeAndConstructionBusiness, Person, WebSite, WebPage, FAQPage
 *
 * @package Kvadratyra
 */

if (!defined('ABSPATH')) exit;

function kv_schema_graph_home(): string {
    $site = home_url('/');
    $name = get_bloginfo('name') ?: 'Квадратура';

    $phone   = get_option('kv_phone', defined('KV_DEFAULT_PHONE') ? KV_DEFAULT_PHONE : '+7 (900) 304-51-90');
    $email   = get_option('kv_email', 'info@kvadratyra.ru');
    $address = get_option('kv_address', 'Волгоградская обл., г. Урюпинск, ул. Штеменко, д. 28');
    $company = get_option('kv_company', $name);
    $inn     = get_option('kv_inn', '');
    $ogrn    = get_option('kv_ogrn', '');
    $hours   = get_option('kv_work_hours', 'Пн–Сб: 09:00–19:00');
    $logo    = '';

    if (has_custom_logo()) {
        $logo_id = get_theme_mod('custom_logo');
        $logo    = wp_get_attachment_image_url($logo_id, 'full') ?: '';
    }
    if (!$logo) {
        $logo = $site . 'wp-content/uploads/logo.png';
    }

    $yandexBusiness = get_option('kv_yandex_business_url', 'https://yandex.ru/profile/13123153536?lang=ru');
    $maxChat = get_option('kv_max_chat_url', defined('KV_DEFAULT_MAX_CHAT') ? KV_DEFAULT_MAX_CHAT : '');
    $okGroup = get_option('kv_ok_group_url', defined('KV_DEFAULT_OK_GROUP') ? KV_DEFAULT_OK_GROUP : '');
    $sameAs = array_filter([
        get_option('kv_telegram', ''),
        $maxChat,
        $okGroup,
        $yandexBusiness,
    ]);

    $graph = [
        /* 1. Organization */
        [
            '@type'       => 'HomeAndConstructionBusiness',
            '@id'         => $site . '#organization',
            'name'        => $name,
            'legalName'   => $company,
            'url'         => $site,
            'logo'        => [
                '@type'      => 'ImageObject',
                'url'        => $logo,
                'contentUrl' => $logo,
            ],
            'telephone'   => preg_replace('/[^+\d]/', '', $phone),
            'email'       => $email,
            'taxID'       => $inn ?: null,
            'identifier'  => array_values(array_filter([
                $inn ? ('ИНН ' . $inn) : null,
                $ogrn ? ('ОГРН/ОГРНИП ' . $ogrn) : null,
            ])),
            'address'     => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $address,
                'addressCountry'  => 'RU',
            ],
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'contactType' => 'customer support',
                    'telephone' => preg_replace('/[^+\d]/', '', $phone),
                    'email' => $email,
                    'availableLanguage' => ['ru'],
                ],
            ],
            'openingHours' => $hours,
            'sameAs'      => array_values($sameAs),
            'areaServed'  => [
                ['@type' => 'AdministrativeArea', 'name' => 'Воронежская область'],
                ['@type' => 'AdministrativeArea', 'name' => 'Саратовская область'],
                ['@type' => 'AdministrativeArea', 'name' => 'Волгоградская область'],
                ['@type' => 'AdministrativeArea', 'name' => 'Тамбовская область'],
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name'  => 'Строительные услуги',
                'itemListElement' => [
                    ['@type' => 'OfferCatalog', 'name' => 'Кровельные работы'],
                    ['@type' => 'OfferCatalog', 'name' => 'Отделка фасадов'],
                    ['@type' => 'OfferCatalog', 'name' => 'Установка заборов'],
                ],
            ],
            'priceRange' => '₽₽',
        ],

        /* 2. Founder */
        [
            '@type'      => 'Person',
            '@id'        => $site . '#founder',
            'name'       => 'Андрей Русских',
            'jobTitle'   => 'Инженер',
            'worksFor'   => ['@id' => $site . '#organization'],
            'description'=> 'Инженер по кровле, фасадам и ограждениям. Опыт 20 лет. Бесплатный замер и смета под ваш объект.',
            'knowsAbout' => ['Кровля', 'Кровельные материалы', 'Профнастил', 'Металлочерепица', 'Фасады', 'Сайдинг', 'Заборы', 'Профлист', 'Монтаж'],
        ],

        /* 3. WebSite */
        [
            '@type'           => 'WebSite',
            '@id'             => $site . '#website',
            'url'             => $site,
            'name'            => $name,
            'description'     => get_bloginfo('description'),
            'inLanguage'      => 'ru-RU',
            'publisher'       => ['@id' => $site . '#organization'],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $site . '?s={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ],

        /* 4. WebPage */
        [
            '@type'     => 'WebPage',
            '@id'       => $site . '#webpage',
            'url'       => $site,
            'name'      => wp_get_document_title(),
            'isPartOf'  => ['@id' => $site . '#website'],
            'about'     => ['@id' => $site . '#organization'],
            'speakable' => [
                '@type'       => 'SpeakableSpecification',
                'cssSelector' => ['.hero__title', '.hero__subtitle', '.section-title'],
            ],
        ],

        /* 5. SoftwareApplication (Calculator) */
        [
            '@type'              => 'SoftwareApplication',
            '@id'                => $site . '#calculator',
            'name'               => 'Калькулятор стоимости кровли, фасадов и заборов',
            'description'        => 'Онлайн-калькулятор для расчёта стоимости монтажа и материалов: кровля, фасад, забор. Мгновенный расчёт с запасом 10%.',
            'applicationCategory'=> 'UtilitiesApplication',
            'operatingSystem'    => 'Web',
            'offers'             => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'RUB',
            ],
            'url'                => $site . '#calculator',
            'provider'           => ['@id' => $site . '#organization'],
        ],

        /* 6. HowTo (Как мы работаем) */
        [
            '@type' => 'HowTo',
            '@id'   => $site . '#howto',
            'name'  => 'Как мы работаем — 5 шагов',
            'step'  => [
                ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Заявка', 'text' => 'Оставляете заявку на сайте, звоните или пишете в Telegram.'],
                ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Бесплатный замер', 'text' => 'Инженер выезжает на объект и фиксирует параметры.'],
                ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Смета и договор', 'text' => 'Согласуем смету и фиксируем цену в договоре.'],
                ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Монтаж', 'text' => 'Бригада выполняет работы по технологии, с контролем качества.'],
                ['@type' => 'HowToStep', 'position' => 5, 'name' => 'Сдача и гарантия', 'text' => 'Сдаём объект, подписываем акт, выдаём гарантию.'],
            ],
        ],
        /* 7. FAQPage (answer-first for YATI/Neuro snippets) */
        [
            '@type' => 'FAQPage',
            '@id'   => $site . '#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name'  => 'Сколько стоит монтаж кровли под ключ?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => 'Точная стоимость зависит от площади, типа покрытия и сложности узлов. После замера готовим смету с фиксированной ценой работ и материалов.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name'  => 'Как быстро вы начинаете работы?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => 'Обычно стартуем в течение 3–7 дней после согласования сметы и подписания договора, при наличии материалов.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name'  => 'Даете ли вы гарантию?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => 'Да, предоставляем письменную гарантию на монтаж и передаем гарантийные документы на материалы от производителя.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name'  => 'Работаете ли вы по соседним городам и районам?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => 'Да, выполняем работы по нескольким областям. География и условия выезда указаны на странице контактов и GEO-страницах.',
                    ],
                ],
            ],
        ],
        /* 8. ItemList: homepage intent navigation */
        [
            '@type' => 'ItemList',
            '@id'   => $site . '#intent-list',
            'name'  => 'Ключевые разделы сайта',
            'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
            'numberOfItems' => 6,
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Кровля', 'url' => home_url('/krovlya/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Фасады', 'url' => home_url('/fasady/')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Заборы', 'url' => home_url('/zabory/')],
                ['@type' => 'ListItem', 'position' => 4, 'name' => 'География работ', 'url' => home_url('/geo/')],
                ['@type' => 'ListItem', 'position' => 5, 'name' => 'Доставка и оплата', 'url' => home_url('/dostavka-oplata/')],
                ['@type' => 'ListItem', 'position' => 6, 'name' => 'Контакты', 'url' => home_url('/kontakty/')],
            ],
        ],
    ];

    $payload = [
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    ];

    return '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
