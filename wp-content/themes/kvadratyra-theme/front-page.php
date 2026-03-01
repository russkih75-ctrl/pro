<?php
/**
 * Front Page Template
 * Sections: Hero → Logistics/Trust → Expert → Services → Guarantees → How-it-works → Calculator → FAQ → Reviews
 *
 * @package Kvadratyra
 */

get_header();
?>

<!-- ===== HERO ===== -->
<section class="section" id="hero" style="padding-top: 40px;">
    <div class="container">
        <div class="bento-grid bento-grid--hero">
            <div class="bento-card bento-card--span-2 animate-in" style="background: var(--bg-alt); justify-content: center;">
                <div class="hero__badge">⚡ Смета с экономией 20% + бесплатный замер</div>
                <h1 class="hero__title" style="font-size: clamp(32px, 4vw, 44px);">
                    Кровля, фасады и заборы <span>напрямую с завода</span>
                </h1>
                <p class="hero__subtitle" style="margin-bottom: 24px;">
                    Своя ж/д ветка, автопарк и производство. Работаем по Воронежской, Тамбовской, Саратовской и Волгоградской областям. Оплата по факту доставки.
                </p>

                <div class="btn-group">
                    <a href="#" class="btn btn--primary btn--lg" data-direct-tg="Здравствуйте! Хочу узнать стоимость материалов и монтажа.">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-5px;"><path d="M22 2L11 13"></path><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        Узнать в Telegram
                    </a>
                    <a href="tel:<?php echo esc_attr(kv_phone(false)); ?>" class="btn btn--lg">
                        Позвонить
                    </a>
                </div>
            </div>

            <div class="bento-card bento-card--span-2 animate-in" style="padding: 0; min-height: 400px;">
                <?php
                $hero_img = 'https://kvadratyra.ru/wp-content/uploads/2026/02/1771694271426-pp1z3vob0v.jpg';
                ?>
                <img src="<?php echo esc_url($hero_img); ?>" alt="Монтаж кровли — профессиональная бригада" width="600" height="600" loading="eager" decoding="async" fetchpriority="high" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <div class="bento-card animate-in text-center" style="display: flex; justify-content: center; background: var(--bg-alt);">
                <div>
                    <div class="hero__stat-num" data-countup="500" data-suffix="+">500+</div>
                    <div class="hero__stat-label">Объектов сдано</div>
                </div>
            </div>
            
            <div class="bento-card animate-in text-center" style="display: flex; justify-content: center; background: var(--bg-alt);">
                <div>
                    <div class="hero__stat-num" data-countup="15" data-suffix=" лет">15 лет</div>
                    <div class="hero__stat-label">Гарантия</div>
                </div>
            </div>
            
            <div class="bento-card animate-in text-center" style="display: flex; justify-content: center; background: var(--bg-alt);">
                <div>
                    <div class="hero__stat-num" data-countup="20" data-suffix="%">20%</div>
                    <div class="hero__stat-label">Экономия на раскрое</div>
                </div>
            </div>

            <div class="bento-card animate-in text-center" style="display: flex; justify-content: center; align-items: center; background: var(--bg-alt);">
                <div style="font-weight: 700; color: var(--accent); font-size: 18px;">
                    Оплата по факту<br><span style="color: var(--text); font-size: 15px;">без скрытых платежей</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== INTENT NAV ===== -->
<section class="section section--intent-nav" id="intent-nav">
    <div class="container">
        <div class="btn-group">
            <a href="#services" class="btn btn--sm">Услуги</a>
            <a href="#logistics" class="btn btn--sm">Доставка и сроки</a>
            <a href="#guarantees" class="btn btn--sm">Гарантии</a>
            <a href="<?php echo esc_url(home_url('/geo/')); ?>" class="btn btn--sm">Города и регионы</a>
            <a href="#faq" class="btn btn--sm">Вопросы и ответы</a>
            <a href="#calculator" class="btn btn--sm">Калькулятор</a>
        </div>
    </div>
</section>

<!-- ===== LOGISTICS & SCALE (The "Meat") ===== -->
<section class="section" id="logistics">
    <div class="container">
        <h2 class="section__title animate-in text-center">Почему у нас дешевле и быстрее</h2>
        <p class="section__subtitle animate-in text-center" style="margin-left:auto;margin-right:auto;">Не посредники. Своя логистика, склады и производство.</p>

        <div class="bento-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
            <div class="bento-card animate-in">
                <div class="card__icon" style="color:var(--accent); background:rgba(0,0,0,0.03);"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16H3"></path><path d="M7 16V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v12"></path><circle cx="6" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle><path d="M12 2v14"></path><path d="M15 6h4a2 2 0 0 1 2 2v8"></path></svg></div>
                <h3 class="card__title">Своя ж/д ветка</h3>
                <div class="card__text">Прямые поставки вагонами с заводов Grand Line и Металл Профиль. Минимальная закупочная цена = лучшая цена для вас.</div>
            </div>
            <div class="bento-card animate-in">
                <div class="card__icon" style="color:var(--accent); background:rgba(0,0,0,0.03);"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg></div>
                <h3 class="card__title">Мощный автопарк</h3>
                <div class="card__text">20 машин Hyundai (6 метров) и 5 фур (20 метров). Доставим любой объем точно в срок, не ожидая наемный транспорт.</div>
            </div>
            <div class="bento-card animate-in">
                <div class="card__icon" style="color:var(--accent); background:rgba(0,0,0,0.03);"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4H2v16z"></path><path d="M17 18h1"></path><path d="M13 18h1"></path><path d="M9 18h1"></path></svg></div>
                <h3 class="card__title">Свое производство</h3>
                <div class="card__text">Линии проката профлиста и металлочерепицы. Режем металл в размер вашего дома — никаких переплат за обрезки.</div>
            </div>
            <div class="bento-card animate-in">
                <div class="card__icon" style="color:var(--accent); background:rgba(0,0,0,0.03);"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path></svg></div>
                <h3 class="card__title">4 региона присутствия</h3>
                <div class="card__text">Офисы и склады в Воронежской, Тамбовской, Саратовской и Волгоградской областях. Мы всегда рядом.</div>
            </div>
            <div class="bento-card animate-in bento-card--span-2" style="background:var(--bg-alt);">
                <div class="card__icon" style="color:var(--accent); background:#fff;"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg></div>
                <h3 class="card__title">Экономия 20%</h3>
                <div class="card__text">Делаем раскрой в инженерной программе. Вы платите только за полезную площадь, а не за "воздух" и обрезки.</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== EXPERT SECTION ===== -->
<section class="section" id="expert" style="background:var(--bg-alt);">
    <div class="container">
        <div class="bento-grid" style="grid-template-columns: 1fr 1fr; align-items: stretch;">
            <div class="bento-card animate-in" style="padding: 0; overflow: hidden;">
                <?php
                $expert_photo = 'https://kvadratyra.ru/wp-content/uploads/2026/02/image_1771694471461_j1v9ep.jpg';
                ?>
                <img
                    src="<?php echo esc_url($expert_photo); ?>"
                    alt="Андрей Русских — инженер по кровле и фасадам"
                    width="600"
                    height="600"
                    loading="lazy"
                    decoding="async"
                    style="width:100%; height:100%; object-fit:cover;"
                >
            </div>
            <div class="bento-card animate-in" style="display:flex; flex-direction:column; justify-content:center;">
                <h2 class="section__title">Почему я не верю онлайн-калькуляторам</h2>
                <p class="section__subtitle" style="margin-bottom: 24px;">(и вам не советую)</p>

                <div class="expert-content__body">
                    <p>
                        Меня зовут <strong>Андрей Русских</strong>. Я инженер по кровле и фасадам, опыт — <strong>20 лет</strong>.
                    </p>
                    <p>
                        Калькулятор на сайте — это игрушка. Он считает "в лоб", с запасом 30%. Он не видит ваших окон, карнизов и сложных примыканий.
                    </p>
                    <p>
                        <strong>Я делаю по-другому.</strong> Я составляю раскладку в инженерной программе. Учитываю каждый сантиметр нахлеста и каждый обрезок.
                    </p>
                    <div class="expert-callout" style="background: rgba(212, 177, 62, 0.1); border-left-color: var(--accent);">
                        <p style="color: var(--text);">
                            Это экономит вам до 20% бюджета. Вы не покупаете лишний металл, который потом будет валяться в гараже.
                        </p>
                    </div>
                    <p>
                        Поэтому: поиграйте с калькулятором, чтобы узнать порядок цен. А за <strong>точной сметой с экономией</strong> приходите ко мне. Замер бесплатный.
                    </p>
                </div>

                <div class="expert-cta mt-3">
                    <a href="#calculator" class="btn btn--primary">
                        Записаться на замер
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== SERVICES ===== -->
<section class="section" id="services">
    <div class="container">
        <h2 class="section__title animate-in text-center">Наши услуги</h2>
        <p class="section__subtitle text-center" style="margin-left:auto;margin-right:auto;">Полный цикл работ с гарантией результата</p>

        <div class="bento-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
            <a href="<?php echo esc_url(home_url('/krovlya/')); ?>" class="bento-card card--link animate-in" style="padding: 0; padding-bottom: 24px;">
                <div class="service-card__img" style="height:220px; background:var(--bg-alt); margin-bottom: 24px;">
                    <!-- Placeholder color block for image -->
                </div>
                <div style="padding: 0 32px;">
                    <div class="service-card__tag" style="background:rgba(0,0,0,0.05); color:var(--text);">Кровля</div>
                    <h3 class="card__title" style="margin-top: 8px;">Монтаж и ремонт кровли</h3>
                    <div class="card__text">Металлочерепица, профнастил, мягкая кровля. Стропильная система, утепление, водостоки.</div>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/fasady/')); ?>" class="bento-card card--link animate-in" style="padding: 0; padding-bottom: 24px;">
                <div class="service-card__img" style="height:220px; background:var(--bg-alt); margin-bottom: 24px;"></div>
                <div style="padding: 0 32px;">
                    <div class="service-card__tag" style="background:rgba(0,0,0,0.05); color:var(--text);">Фасады</div>
                    <h3 class="card__title" style="margin-top: 8px;">Отделка фасадов</h3>
                    <div class="card__text">Сайдинг, фасадные панели, штукатурка. Утепление стен (минвата, пеноплекс).</div>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/zabory/')); ?>" class="bento-card card--link animate-in" style="padding: 0; padding-bottom: 24px;">
                <div class="service-card__img" style="height:220px; background:var(--bg-alt); margin-bottom: 24px;"></div>
                <div style="padding: 0 32px;">
                    <div class="service-card__tag" style="background:rgba(0,0,0,0.05); color:var(--text);">Заборы</div>
                    <h3 class="card__title" style="margin-top: 8px;">Установка заборов</h3>
                    <div class="card__text">Профнастил, евроштакетник, 3D-сетка. Ленточный фундамент, кирпичные столбы, ворота.</div>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- ===== IRON GUARANTEES ===== -->
<section class="section" id="guarantees" style="background:var(--bg-alt);">
    <div class="container">
        <h2 class="section__title animate-in text-center">Железные гарантии</h2>
        <div class="bento-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-top: 40px;">
            <div class="bento-card animate-in text-center">
                <h3 class="card__title" style="color:var(--accent); font-size: 24px; font-weight: 900;">15 лет гарантии</h3>
                <div class="card__text">Прописываем в договоре гарантию на материалы и монтажные работы. Мы уверены в качестве.</div>
            </div>
            <div class="bento-card animate-in text-center">
                <h3 class="card__title" style="color:var(--accent); font-size: 24px; font-weight: 900;">Оплата по факту</h3>
                <div class="card__text">Никаких авансов за "воздух". Привезли материал — вы оплатили. Сделали работу — вы приняли и оплатили.</div>
            </div>
            <div class="bento-card animate-in text-center">
                <h3 class="card__title" style="color:var(--accent); font-size: 24px; font-weight: 900;">Отвечаем за замер</h3>
                <div class="card__text">Мы замеряем — мы отвечаем. Если инженер ошибся в расчетах, докупаем и привозим материал за свой счет.</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="section" id="how-it-works">
    <div class="container">
        <h2 class="section__title animate-in text-center">Как мы работаем</h2>
        <div class="bento-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-top: 40px;">
            <div class="bento-card animate-in" style="background:var(--bg-alt);">
                <div class="timeline__icon" style="position:relative; margin-bottom: 20px; box-shadow:none;"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></div>
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px; color: var(--accent);">1. Заявка</h3>
                <p style="color: var(--muted); font-size: 15px;">Оставьте заявку на сайте. Инженер перезвонит в течение 15 минут.</p>
            </div>
            <div class="bento-card animate-in" style="background:var(--bg-alt);">
                <div class="timeline__icon" style="position:relative; margin-bottom: 20px; box-shadow:none;"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div>
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px; color: var(--accent);">2. Бесплатный замер</h3>
                <p style="color: var(--muted); font-size: 15px;">Инженер приезжает, замеряет, показывает образцы материалов вживую.</p>
            </div>
            <div class="bento-card animate-in" style="background:var(--bg-alt);">
                <div class="timeline__icon" style="position:relative; margin-bottom: 20px; box-shadow:none;"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px; color: var(--accent);">3. Точная смета</h3>
                <p style="color: var(--muted); font-size: 15px;">Делаем раскрой в программе. Фиксируем цену и сроки в договоре.</p>
            </div>
            <div class="bento-card animate-in" style="background:var(--bg-alt);">
                <div class="timeline__icon" style="position:relative; margin-bottom: 20px; box-shadow:none;"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px; color: var(--accent);">4. Монтаж и оплата</h3>
                <p style="color: var(--muted); font-size: 15px;">Привозим материал, монтируем. Вы принимаете работу и оплачиваете по факту.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== ANSWER FIRST ===== -->
<section class="section" id="answer-first" style="background:var(--bg-alt);">
    <div class="container">
        <h2 class="section__title animate-in text-center">Коротко по делу: что важно перед заказом</h2>
        <div class="bento-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-top: 40px;">
            <div class="bento-card">
                <h3 class="card__title">Сколько стоит?</h3>
                <div class="card__text">Ориентиры по цене даём сразу, точную смету — после бесплатного замера с раскроем в инженерной программе.</div>
            </div>
            <div class="bento-card">
                <h3 class="card__title">Когда начнёте?</h3>
                <div class="card__text">Обычно 3–7 дней после согласования сметы. Материалы поставляем своим транспортом по 4 областям.</div>
            </div>
            <div class="bento-card">
                <h3 class="card__title">Кто отвечает за результат?</h3>
                <div class="card__text">Работаем по договору, фиксируем цену, даём гарантию на монтаж и передаём гарантийные документы на материалы.</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== QUIZ / CALCULATOR ===== -->
<?php get_template_part('template-parts/quiz-calculator'); ?>

<!-- ===== FAQ ===== -->
<section class="section" id="faq">
    <div class="container">
        <h2 class="section__title animate-in">Частые вопросы</h2>
        <?php get_template_part('template-parts/faq-block'); ?>
    </div>
</section>

<!-- ===== REVIEWS ===== -->
<section class="section" id="reviews">
    <div class="container">
        <?php get_template_part('template-parts/reviews-slider'); ?>
    </div>
</section>

<!-- ===== FINAL CTA ===== -->
<section class="section" style="text-align:center;">
    <div class="container">
        <div class="card" style="padding:48px 32px;max-width:700px;margin:0 auto;">
            <h2 class="section__title mb-2">Готовы сэкономить 20%?</h2>
            <p class="section__subtitle mb-3" style="margin-left:auto;margin-right:auto;">
                Запишитесь на бесплатный замер. Инженер приедет с образцами и сделает точный расчет.
            </p>
            <div class="btn-group" style="justify-content:center;">
                <a href="tel:<?php echo esc_attr(kv_phone(false)); ?>" class="btn btn--primary btn--lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-5px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    Вызвать инженера
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
