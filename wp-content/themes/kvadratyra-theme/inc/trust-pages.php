<?php
/**
 * Auto-create trust pages (white SEO baseline):
 * - contacts
 * - delivery/payment
 * - warranty
 * - requisites
 * - privacy policy
 *
 * Runs on theme activation only.
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;

function kv_trust_default_company_name(): string {
    return (string) (get_bloginfo('name') ?: 'Квадратура');
}

function kv_trust_build_page_content(string $type): string {
    if ($type === 'o-nas') {
        return
            '<h2>О компании</h2>' .
            '<p>Мы специализируемся на кровле, фасадах и заборах под ключ. Работаем с частными домами и коммерческими объектами, соблюдаем технологию монтажа и фиксируем условия в договоре.</p>' .
            '<p>Перед стартом работ делаем бесплатный замер, подбираем материалы под бюджет и задачи, формируем смету с поэтапным планом.</p>' .
            '<h2>Почему нам доверяют</h2>' .
            '<p><strong>Прозрачная смета:</strong> без скрытых доплат и «сюрпризов» после начала работ.</p>' .
            '<p><strong>Контроль качества:</strong> проверяем узлы монтажа на каждом этапе.</p>' .
            '<p><strong>Гарантия:</strong> письменные гарантийные обязательства на работы и материалы.</p>' .
            '<p><strong>Региональная экспертиза:</strong> учитываем климат и ветровые/снеговые нагрузки по регионам работ.</p>';
    }

    if ($type === 'vakansii') {
        return
            '<h2>Вакансии</h2>' .
            '<p>Мы регулярно расширяем бригады и приглашаем специалистов по кровле, фасадам и ограждениям.</p>' .
            '<h3>Актуальные направления</h3>' .
            '<p><strong>Кровельщик</strong> — монтаж металлочерепицы, профнастила, мягкой кровли.</p>' .
            '<p><strong>Фасадчик</strong> — сайдинг, фасадные панели, утепление.</p>' .
            '<p><strong>Монтажник заборов</strong> — профнастил, евроштакетник, 3D-сетка.</p>' .
            '<h3>Условия</h3>' .
            '<p>Официальный договор, стабильная загрузка в сезон, своевременная выплата, обеспечение материалом и объектами.</p>' .
            '<p>Отклик: телефон, Telegram или email в разделе «Контакты».</p>';
    }

    $company = get_option('kv_company', kv_trust_default_company_name());
    $phone   = get_option('kv_phone', '+7 (XXX) XXX-XX-XX');
    $email   = get_option('kv_email', 'info@kvadratyra.ru');
    $tg      = get_option('kv_telegram', '');
    $address = get_option('kv_address', 'Волгоградская обл., г. Урюпинск, ул. Штеменко, д. 28');
    $inn     = get_option('kv_inn', '');
    $ogrn    = get_option('kv_ogrn', '');
    $hours   = get_option('kv_work_hours', 'Пн–Сб: 09:00–19:00');
    $yandexBusiness = get_option('kv_yandex_business_url', '');
    $maxChat = get_option('kv_max_chat_url', defined('KV_DEFAULT_MAX_CHAT') ? KV_DEFAULT_MAX_CHAT : '');
    $okGroup = get_option('kv_ok_group_url', defined('KV_DEFAULT_OK_GROUP') ? KV_DEFAULT_OK_GROUP : '');

    // Keep content HTML-simple (no lists tags requirement is only for WP create_post tool; here it's local theme code).
    if ($type === 'kontakty') {
        $mapsUrl = $address ? ('https://yandex.ru/maps/?text=' . rawurlencode($address)) : '';
        return
            '<h2>Связаться с нами</h2>' .
            '<p>Свяжитесь любым удобным способом — ответим в течение 15 минут в рабочее время.</p>' .
            '<p><strong>Телефон:</strong> <a href="tel:' . esc_attr(preg_replace('/[^+\d]/', '', $phone)) . '">' . esc_html($phone) . '</a></p>' .
            '<p><strong>Email:</strong> <a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>' .
            ($tg ? '<p><strong>Telegram:</strong> <a href="' . esc_url($tg) . '" target="_blank" rel="noopener">' . esc_html($tg) . '</a></p>' : '') .
            '<p><strong>Адрес:</strong> ' . esc_html($address) . '</p>' .
            '<p><strong>Режим работы:</strong> ' . esc_html($hours) . '</p>' .
            ($mapsUrl ? '<p><strong>На карте:</strong> <a href="' . esc_url($mapsUrl) . '" target="_blank" rel="noopener">Открыть в Яндекс.Картах</a></p>' : '') .
            ($yandexBusiness ? '<p><strong>Яндекс.Бизнес:</strong> <a href="' . esc_url($yandexBusiness) . '" target="_blank" rel="noopener">Карточка организации</a></p>' : '') .
            ($maxChat ? '<p><strong>MAX:</strong> <a href="' . esc_url($maxChat) . '" target="_blank" rel="noopener">Чат поддержки</a></p>' : '') .
            ($okGroup ? '<p><strong>Одноклассники:</strong> <a href="' . esc_url($okGroup) . '" target="_blank" rel="noopener">Наш канал в ОК</a></p>' : '') .
            '<h2>Реквизиты компании</h2>' .
            '<p><strong>Организация:</strong> ' . esc_html($company) . '</p>' .
            ($inn ? '<p><strong>ИНН:</strong> ' . esc_html($inn) . '</p>' : '') .
            ($ogrn ? '<p><strong>ОГРН/ОГРНИП:</strong> ' . esc_html($ogrn) . '</p>' : '') .
            '<h2>География работ</h2>' .
            '<p>Работаем по 4 областям Центрального и Приволжского федеральных округов:</p>' .
            '<p><strong>Воронежская область</strong> — Борисоглебск, Анна, Новохоперск, Поворино, Грибановский, Эртиль и другие населённые пункты.</p>' .
            '<p><strong>Тамбовская область</strong> — Тамбов, Рассказово, Моршанск, Мичуринск, Кирсанов, Уварово.</p>' .
            '<p><strong>Саратовская область</strong> — Балашов, Аркадак, Романовка, Турки, Самойловка, Калининск, Ртищево.</p>' .
            '<p><strong>Волгоградская область</strong> — Михайловка, Урюпинск, Новоаннинский, Камышин, Фролово, Жирновск.</p>' .
            '<p>Выезд инженера на замер — <strong>бесплатно</strong> в пределах рабочей зоны. Для удалённых объектов условия уточняйте у менеджера.</p>';
    }

    if ($type === 'dostavka-oplata') {
        return
            '<h2>Доставка материалов на объект</h2>' .
            '<p>Доставка строительных и кровельных материалов организуется напрямую от производителей (Grand Line, Технониколь, Металл Профиль) до вашего объекта.</p>' .
            '<h3>Сроки и стоимость доставки</h3>' .
            '<table><thead><tr><th>Зона</th><th>Расстояние</th><th>Срок</th><th>Стоимость</th></tr></thead>' .
            '<tbody>' .
            '<tr><td>Борисоглебск и район (до 30 км)</td><td>0–30 км</td><td>1 рабочий день</td><td>Бесплатно (при заказе монтажа)</td></tr>' .
            '<tr><td>Воронежская область</td><td>30–150 км</td><td>1–2 рабочих дня</td><td>от 3 000 руб.</td></tr>' .
            '<tr><td>Тамбовская / Саратовская область</td><td>150–350 км</td><td>2–3 рабочих дня</td><td>от 5 000 руб.</td></tr>' .
            '<tr><td>Волгоградская область</td><td>350–500 км</td><td>3–5 рабочих дней</td><td>от 7 000 руб.</td></tr>' .
            '</tbody></table>' .
            '<p>Точная стоимость доставки рассчитывается при оформлении заказа. При большом объёме материалов возможна бесплатная доставка — уточняйте у менеджера.</p>' .
            '<h3>Что входит в доставку</h3>' .
            '<p><strong>Разгрузка:</strong> организуем разгрузку краном-манипулятором (при необходимости). <strong>Упаковка:</strong> все материалы доставляются в заводской упаковке. <strong>Документы:</strong> товарная накладная, сертификаты качества.</p>' .
            '<h2>Оплата</h2>' .
            '<p>Мы предлагаем несколько вариантов оплаты:</p>' .
            '<p><strong>Предоплата 50% + 50% по факту.</strong> Самый популярный вариант. 50% — при подписании договора, 50% — после приёмки работ.</p>' .
            '<p><strong>Постоплата.</strong> Для постоянных клиентов и юридических лиц — по факту выполнения работ (по согласованию).</p>' .
            '<p><strong>Рассрочка.</strong> Возможна рассрочка на 3–6 месяцев без переплаты — условия обсуждаются индивидуально.</p>' .
            '<p>Принимаем: наличные, банковский перевод, оплату по QR-коду (СБП). Для юрлиц — безналичный расчёт с НДС.</p>' .
            '<p>Все условия фиксируются в договоре и смете до начала работ.</p>';
    }

    if ($type === 'garantiya') {
        return
            '<div class="trust-landing">' .
            '<div class="trust-hero" style="background:var(--bg-alt); padding:40px; border-radius:var(--radius); margin-bottom:40px; text-align:center;">' .
            '<h2 style="margin-bottom:16px;">Железная гарантия до 15 лет</h2>' .
            '<p style="font-size:18px; color:var(--muted); max-width:600px; margin:0 auto;">Мы предоставляем письменную гарантию на все виды работ и материалов. Гарантийные обязательства фиксируются в официальном договоре подряда, а не на словах.</p>' .
            '</div>' .
            
            '<div class="grid grid--2" style="margin-bottom:40px;">' .
            '<div class="card">' .
            '<div style="color:var(--accent); margin-bottom:16px;"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>' .
            '<h3>Гарантия на монтаж (до 10 лет)</h3>' .
            '<p>Покрывает качество сборки, герметичность соединений, соблюдение технологии крепежа и отсутствие протечек.</p>' .
            '</div>' .
            '<div class="card">' .
            '<div style="color:var(--accent); margin-bottom:16px;"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>' .
            '<h3>Гарантия на материалы (до 50 лет)</h3>' .
            '<p>Обеспечивается заводом-производителем. Включает сохранение цвета, стойкость к УФ, отсутствие сквозной коррозии.</p>' .
            '</div>' .
            '</div>' .
            
            '<h3 style="margin-top:40px;">Сроки гарантии по видам работ</h3>' .
            '<table class="trust-table" style="width:100%; text-align:left; border-collapse:collapse; margin-bottom:40px;">' .
            '<thead><tr style="border-bottom:2px solid var(--border);"><th>Вид работ</th><th>На монтаж</th><th>На материал</th></tr></thead>' .
            '<tbody>' .
            '<tr style="border-bottom:1px solid var(--border);"><td>Кровля (металлочерепица, профнастил)</td><td>до 10 лет</td><td>до 50 лет</td></tr>' .
            '<tr style="border-bottom:1px solid var(--border);"><td>Мягкая кровля</td><td>до 10 лет</td><td>до 30 лет</td></tr>' .
            '<tr style="border-bottom:1px solid var(--border);"><td>Фасады (сайдинг, панели)</td><td>до 5 лет</td><td>до 30 лет</td></tr>' .
            '<tr style="border-bottom:1px solid var(--border);"><td>Заборы (профнастил, штакетник)</td><td>до 5 лет</td><td>до 25 лет</td></tr>' .
            '</tbody></table>' .
            
            '<div class="trust-steps" style="margin-bottom:40px;">' .
            '<h3>Гарантийный случай — что делать?</h3>' .
            '<div class="grid grid--3">' .
            '<div class="card" style="padding:24px;">' .
            '<div style="font-size:24px; font-weight:800; color:var(--muted); margin-bottom:12px;">01</div>' .
            '<p>Свяжитесь с нами по телефону или отправьте фото в Telegram.</p>' .
            '</div>' .
            '<div class="card" style="padding:24px;">' .
            '<div style="font-size:24px; font-weight:800; color:var(--muted); margin-bottom:12px;">02</div>' .
            '<p>Мы бесплатно организуем выезд инженера для осмотра в течение 2-3 дней.</p>' .
            '</div>' .
            '<div class="card" style="padding:24px;">' .
            '<div style="font-size:24px; font-weight:800; color:var(--muted); margin-bottom:12px;">03</div>' .
            '<p>Устраняем дефект полностью за наш счёт в течение 14 рабочих дней.</p>' .
            '</div>' .
            '</div>' .
            '</div>' .
            
            '<div class="trust-cta" style="background:var(--bg-alt); padding:32px; border-radius:var(--radius); text-align:center;">' .
            '<h3 style="margin-bottom:16px;">Нужна надежная крыша с письменной гарантией?</h3>' .
            '<p style="margin-bottom:24px;">Запишитесь на замер, и мы привезем образцы договоров и сертификатов.</p>' .
            '<a href="/#calculator" class="btn btn--primary">Рассчитать стоимость и вызвать инженера</a>' .
            '</div>' .
            '</div>';
    }

    if ($type === 'rekvizity') {
        $lines = '<h2>Реквизиты организации</h2>';
        $lines .= '<table><tbody>';
        $lines .= '<tr><td><strong>Полное наименование</strong></td><td>' . esc_html($company) . '</td></tr>';
        if ($inn)  $lines .= '<tr><td><strong>ИНН</strong></td><td>' . esc_html($inn) . '</td></tr>';
        if ($ogrn) $lines .= '<tr><td><strong>ОГРН/ОГРНИП</strong></td><td>' . esc_html($ogrn) . '</td></tr>';
        if ($address) $lines .= '<tr><td><strong>Юридический адрес</strong></td><td>' . esc_html($address) . '</td></tr>';
        if ($phone) $lines .= '<tr><td><strong>Телефон</strong></td><td>' . esc_html($phone) . '</td></tr>';
        if ($email) $lines .= '<tr><td><strong>Email</strong></td><td>' . esc_html($email) . '</td></tr>';
        $lines .= '</tbody></table>';
        $lines .= '<h2>Документы</h2>';
        $lines .= '<p>По запросу предоставляем: договор подряда, смету, акт выполненных работ, счёт-фактуру, акт сверки, гарантийный талон, сертификаты на материалы.</p>';
        $lines .= '<p>Для юридических лиц — полный комплект закрывающих документов для бухгалтерии.</p>';
        return $lines;
    }

    // politika-konfidencialnosti
    return
        '<h2>Политика конфиденциальности</h2>' .
        '<p>Мы собираем и обрабатываем персональные данные только в целях связи с клиентом, расчёта сметы и исполнения договора.</p>' .
        '<p>Данные не передаются третьим лицам, кроме случаев, предусмотренных законом, либо необходимых для оказания услуги (доставка, оплата).</p>' .
        '<p>По запросу вы можете уточнить, изменить или удалить ваши данные, связавшись с нами.</p>';
}

function kv_trust_ensure_page(string $slug, string $title, string $content_html): void {
    $existing = get_page_by_path($slug);
    if ($existing instanceof WP_Post) return;

    wp_insert_post([
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content_html,
    ]);
}

add_action('after_switch_theme', function () {
    kv_trust_ensure_page('o-nas', 'О компании', kv_trust_build_page_content('o-nas'));
    kv_trust_ensure_page('kontakty', 'Контакты', kv_trust_build_page_content('kontakty'));
    kv_trust_ensure_page('dostavka-oplata', 'Доставка и оплата', kv_trust_build_page_content('dostavka-oplata'));
    kv_trust_ensure_page('garantiya', 'Гарантия', kv_trust_build_page_content('garantiya'));
    kv_trust_ensure_page('rekvizity', 'Реквизиты', kv_trust_build_page_content('rekvizity'));
    kv_trust_ensure_page('vakansii', 'Вакансии', kv_trust_build_page_content('vakansii'));
    kv_trust_ensure_page('politika-konfidencialnosti', 'Политика конфиденциальности', kv_trust_build_page_content('politika-konfidencialnosti'));
});

/**
 * One-shot seeding for already active theme (runs once, then disables itself).
 */
add_action('init', function () {
    $seed_version = 2;
    $must_ensure_missing = !(
        get_page_by_path('o-nas') instanceof WP_Post &&
        get_page_by_path('vakansii') instanceof WP_Post &&
        get_page_by_path('kontakty') instanceof WP_Post &&
        get_page_by_path('dostavka-oplata') instanceof WP_Post &&
        get_page_by_path('garantiya') instanceof WP_Post &&
        get_page_by_path('rekvizity') instanceof WP_Post &&
        get_page_by_path('politika-konfidencialnosti') instanceof WP_Post
    );
    if ((int) get_option('kv_trust_pages_seed_version', 0) >= $seed_version && !$must_ensure_missing) return;

    kv_trust_ensure_page('o-nas', 'О компании', kv_trust_build_page_content('o-nas'));
    kv_trust_ensure_page('kontakty', 'Контакты', kv_trust_build_page_content('kontakty'));
    kv_trust_ensure_page('dostavka-oplata', 'Доставка и оплата', kv_trust_build_page_content('dostavka-oplata'));
    kv_trust_ensure_page('garantiya', 'Гарантия', kv_trust_build_page_content('garantiya'));
    kv_trust_ensure_page('rekvizity', 'Реквизиты', kv_trust_build_page_content('rekvizity'));
    kv_trust_ensure_page('vakansii', 'Вакансии', kv_trust_build_page_content('vakansii'));
    kv_trust_ensure_page('politika-konfidencialnosti', 'Политика конфиденциальности', kv_trust_build_page_content('politika-konfidencialnosti'));

    update_option('kv_trust_pages_seeded', '1', false);
    update_option('kv_trust_pages_seed_version', $seed_version, false);
}, 20);

