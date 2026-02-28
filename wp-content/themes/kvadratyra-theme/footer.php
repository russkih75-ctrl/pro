<?php
/**
 * Footer template — 4-column layout
 *
 * @package Kvadratyra
 */
?>
</main><!-- #site-main -->

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Column 1: Brand -->
            <div class="footer-col">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo">
                    <?php if (has_custom_logo()) : ?>
                        <?php
                        $logo_id = get_theme_mod('custom_logo');
                        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
                        ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" width="140" height="32">
                    <?php else : ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M3 21V9l9-7 9 7v12H3z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M9 21V13h6v8" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                        <span><?php bloginfo('name'); ?></span>
                    <?php endif; ?>
                </a>
                <p class="footer-brand__desc">
                    Кровля, фасады, заборы — профессиональный монтаж и качественные материалы. Работаем по Воронежской, Тамбовской, Саратовской и Волгоградской областям.
                </p>
            </div>

            <!-- Column 2: Services -->
            <div class="footer-col">
                <div class="footer-col__title">Услуги</div>
                <?php
                if (has_nav_menu('footer-1')) {
                    wp_nav_menu([
                        'theme_location' => 'footer-1',
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'walker'         => new KV_Nav_Walker(),
                        'depth'          => 1,
                    ]);
                } else {
                    ?>
                    <a href="<?php echo esc_url(home_url('/krovlya/')); ?>">Монтаж кровли</a>
                    <a href="<?php echo esc_url(home_url('/fasady/')); ?>">Отделка фасадов</a>
                    <a href="<?php echo esc_url(home_url('/zabory/')); ?>">Установка заборов</a>
                    <a href="<?php echo esc_url(home_url('/zamery/')); ?>">Бесплатный замер</a>
                    <a href="<?php echo esc_url(home_url('/materialy/')); ?>">Материалы</a>
                    <?php
                }
                ?>
            </div>

            <!-- Column 3: Info -->
            <div class="footer-col">
                <div class="footer-col__title">Информация</div>
                <?php
                if (has_nav_menu('footer-2')) {
                    wp_nav_menu([
                        'theme_location' => 'footer-2',
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'walker'         => new KV_Nav_Walker(),
                        'depth'          => 1,
                    ]);
                } else {
                    ?>
                    <a href="<?php echo esc_url(home_url('/o-nas/')); ?>">О компании</a>
                    <a href="<?php echo esc_url(home_url('/portfolio/')); ?>">Портфолио</a>
                    <a href="<?php echo esc_url(home_url('/otzyvy/')); ?>">Отзывы</a>
                    <a href="<?php echo esc_url(home_url('/blog/')); ?>">Блог</a>
                    <a href="<?php echo esc_url(home_url('/guides/')); ?>">Гайды</a>
                    <a href="<?php echo esc_url(home_url('/geo/')); ?>">География</a>
                    <a href="<?php echo esc_url(home_url('/kontakty/')); ?>">Контакты</a>
                    <a href="<?php echo esc_url(home_url('/dostavka-oplata/')); ?>">Доставка и оплата</a>
                    <a href="<?php echo esc_url(home_url('/garantiya/')); ?>">Гарантия</a>
                    <a href="<?php echo esc_url(home_url('/rekvizity/')); ?>">Реквизиты</a>
                    <a href="<?php echo esc_url(home_url('/politika-konfidencialnosti/')); ?>">Политика конфиденциальности</a>
                    <?php
                }
                ?>
            </div>

            <!-- Column 4: Contacts (itemscope for commercial trust signals) -->
            <div class="footer-col" itemscope itemtype="https://schema.org/HomeAndConstructionBusiness">
                <meta itemprop="name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <meta itemprop="url" content="<?php echo esc_url(home_url('/')); ?>">
                <div class="footer-col__title">Контакты</div>

                <?php $phone = kv_phone(); ?>
                <a href="tel:<?php echo esc_attr(kv_phone(false)); ?>" itemprop="telephone"><?php echo esc_html($phone); ?></a>

                <?php $email = get_option('kv_email', ''); ?>
                <?php if ($email) : ?>
                    <a href="mailto:<?php echo esc_attr($email); ?>" itemprop="email"><?php echo esc_html($email); ?></a>
                <?php endif; ?>

                <?php $address = get_option('kv_address', 'Волгоградская обл., г. Урюпинск, ул. Штеменко, д. 28'); ?>
                <?php if ($address) : ?>
                    <p class="footer-contact__item" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                        <span itemprop="streetAddress"><?php echo esc_html($address); ?></span>
                        <meta itemprop="addressCountry" content="RU">
                    </p>
                <?php endif; ?>

                <?php $hours = get_option('kv_work_hours', 'Пн–Сб: 09:00–19:00'); ?>
                <?php if ($hours) : ?>
                    <p class="footer-contact__hours">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span itemprop="openingHours" content="Mo-Sa 09:00-19:00"><?php echo esc_html($hours); ?></span>
                    </p>
                <?php endif; ?>

                <div class="footer-messengers">
                    <?php $tg = get_option('kv_telegram', ''); ?>
                    <?php if ($tg) : ?>
                        <a href="<?php echo esc_url($tg); ?>" target="_blank" rel="noopener" aria-label="Telegram" itemprop="sameAs">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.665 3.717l-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l.002.001-.314 4.692c.46 0 .663-.211.921-.46l2.211-2.15 4.599 3.397c.848.467 1.457.227 1.668-.785l3.019-14.228c.309-1.239-.473-1.8-1.282-1.434z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php $wa = get_option('kv_whatsapp', ''); ?>
                    <?php if ($wa) : ?>
                        <a href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener" aria-label="WhatsApp" itemprop="sameAs">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    <?php endif; ?>
                </div>

                <?php $yb = get_option('kv_yandex_business_url', ''); ?>
                <?php if ($yb) : ?>
                    <a href="<?php echo esc_url($yb); ?>" target="_blank" rel="noopener" class="footer-link--secondary" itemprop="sameAs">
                        Карточка в Яндекс.Бизнес
                    </a>
                <?php endif; ?>

                <?php $maxChat = get_option('kv_max_chat_url', defined('KV_DEFAULT_MAX_CHAT') ? KV_DEFAULT_MAX_CHAT : ''); ?>
                <?php if ($maxChat) : ?>
                    <a href="<?php echo esc_url($maxChat); ?>" target="_blank" rel="noopener" class="footer-link--secondary" itemprop="sameAs">
                        Чат в MAX
                    </a>
                <?php endif; ?>

                <?php $okGroup = get_option('kv_ok_group_url', defined('KV_DEFAULT_OK_GROUP') ? KV_DEFAULT_OK_GROUP : ''); ?>
                <?php if ($okGroup) : ?>
                    <a href="<?php echo esc_url($okGroup); ?>" target="_blank" rel="noopener" class="footer-link--secondary" itemprop="sameAs">
                        Канал в ОК
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- REQUISITES BLOCK -->
        <?php
        $req_company = trim((string) get_option('kv_company', 'ИП Русских Андрей Алексеевич'));
        $req_inn = trim((string) get_option('kv_inn', '343202958700'));
        $req_ogrn = trim((string) get_option('kv_ogrn', '322344300063232'));
        $req_address = trim((string) get_option('kv_address', 'Волгоградская обл., г. Урюпинск, ул. Штеменко, д. 28'));
        ?>
        <?php if ($req_company || $req_inn || $req_ogrn || $req_address) : ?>
            <div class="footer-requisites">
                <p>
                    <strong>Реквизиты:</strong>
                    <?php if ($req_company) echo esc_html($req_company); ?>
                    <?php if ($req_inn) echo ', ИНН ' . esc_html($req_inn); ?>
                    <?php if ($req_ogrn) echo ', ОГРН/ОГРНИП ' . esc_html($req_ogrn); ?>
                </p>
                <?php if ($req_address) : ?>
                    <p>Адрес: <?php echo esc_html($req_address); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Bottom bar -->
        <div class="footer-bottom">
            <div>
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Все права защищены.
            </div>
            <div class="footer-socials">
                <?php $tg = get_option('kv_telegram', ''); ?>
                <?php if ($tg) : ?>
                    <a href="<?php echo esc_url($tg); ?>" target="_blank" rel="noopener" aria-label="Telegram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.665 3.717l-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l.002.001-.314 4.692c.46 0 .663-.211.921-.46l2.211-2.15 4.599 3.397c.848.467 1.457.227 1.668-.785l3.019-14.228c.309-1.239-.473-1.8-1.282-1.434z"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<!-- Floating CTA -->
<div class="floating-cta">
    <?php if ($tg) : ?>
        <a href="<?php echo esc_url($tg); ?>" class="floating-cta__btn floating-cta__btn--tg" target="_blank" rel="noopener" aria-label="Написать в Telegram">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.665 3.717l-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l.002.001-.314 4.692c.46 0 .663-.211.921-.46l2.211-2.15 4.599 3.397c.848.467 1.457.227 1.668-.785l3.019-14.228c.309-1.239-.473-1.8-1.282-1.434z"/>
            </svg>
        </a>
    <?php endif; ?>

    <button class="floating-cta__btn floating-cta__btn--top" id="kv-scroll-top" aria-label="Наверх">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>
</div>

<!-- Partner booking dialog (GEO) -->
<dialog class="kv-dialog" id="kv-partner-dialog" aria-labelledby="kv-partner-title">
    <div class="kv-dialog__inner">
        <div class="kv-dialog__header">
            <div>
                <div class="kv-dialog__title" id="kv-partner-title">Пункт оформления заказов</div>
                <div class="kv-dialog__sub" data-kv-partner-sub>Напишите в Telegram — пришлём точный адрес и маршрут, подскажем по образцам и наличию.</div>
            </div>
            <button type="button" class="kv-dialog__close" data-kv-partner-close aria-label="Закрыть">×</button>
        </div>

        <form class="kv-dialog__form" id="kv-partner-form">
            <input type="hidden" name="route_url" data-kv-partner-route-url>
            <input type="hidden" name="point_id" data-kv-partner-point-id>
            <input type="hidden" name="city" data-kv-partner-city>
            <input type="hidden" name="city_slug" data-kv-partner-city-slug>
            <input type="hidden" name="region_slug" data-kv-partner-region-slug>
            <input type="hidden" name="service_slug" data-kv-partner-service-slug>
            <input type="hidden" name="area_hint" data-kv-partner-area-hint>

            <div class="kv-dialog__grid" data-kv-partner-fields>
                <label class="kv-field">
                    <span class="kv-field__label">Дата</span>
                    <input type="date" name="date" required data-kv-partner-date>
                </label>
                <label class="kv-field">
                    <span class="kv-field__label">Время</span>
                    <select name="time_slot" required data-kv-partner-time>
                        <option value="">Выберите…</option>
                        <option value="10:00">10:00</option>
                        <option value="12:00">12:00</option>
                        <option value="15:00">15:00</option>
                        <option value="18:00">18:00</option>
                    </select>
                </label>
                <label class="kv-field" style="grid-column:1 / -1;">
                    <span class="kv-field__label">Комментарий (необязательно)</span>
                    <input type="text" name="comment" placeholder="Что хотите посмотреть: цвет, покрытие, толщину" autocomplete="off" data-kv-partner-comment>
                </label>
                <label class="kv-field" style="grid-column:1 / -1;">
                    <span class="kv-field__label" style="font-size:13px;">
                        <input type="checkbox" required data-kv-partner-consent>
                        Нажимая «Написать в Telegram», вы соглашаетесь с обработкой персональных данных и политикой конфиденциальности.
                    </span>
                </label>
            </div>

            <div class="kv-dialog__actions" data-kv-partner-actions>
                <button type="submit" class="btn btn--primary" data-kv-partner-submit>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Написать в Telegram
                </button>
            </div>

            <div class="kv-dialog__success" hidden data-kv-partner-success>
                <div class="kv-dialog__success-title">Telegram открыт</div>
                <div class="kv-dialog__success-text">Отправьте сообщение — пришлём точный адрес и маршрут, уточним наличие образцов.</div>
                <div class="kv-dialog__actions">
                    <a href="#" class="btn btn--primary" data-kv-partner-open-after>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><circle cx="12" cy="10" r="3"></circle><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"></path></svg>
                        Открыть маршрут в Яндекс.Картах
                    </a>
                    <button type="button" class="btn" data-kv-partner-close2>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Закрыть
                    </button>
                </div>
            </div>
        </form>
    </div>
</dialog>

<?php wp_footer(); ?>
</body>
</html>
