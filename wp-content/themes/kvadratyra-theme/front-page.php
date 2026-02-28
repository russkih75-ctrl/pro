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
<section class="hero" id="hero">
    <div class="container">
        <div class="hero__inner">
            <div class="hero__content">
                <div class="hero__badge">⚡ Смета с экономией 20% + бесплатный замер</div>
                <h1 class="hero__title">
                    Кровля, фасады и заборы <span>напрямую с завода</span>
                </h1>
                <p class="hero__subtitle">
                    Своя ж/д ветка, автопарк и производство. Работаем по Воронежской, Тамбовской, Саратовской и Волгоградской областям. Оплата по факту доставки.
                </p>

                <div class="btn-group">
                    <a href="#" class="btn btn--primary btn--lg" data-direct-tg="Здравствуйте! Хочу узнать стоимость материалов и монтажа.">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-5px;"><path d="M22 2L11 13"></path><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        Узнать стоимость в Telegram
                    </a>
                    <a href="tel:<?php echo esc_attr(kv_phone(false)); ?>" class="btn btn--lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;vertical-align:-5px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        Позвонить
                    </a>
                </div>

                <div class="hero__trust">
                    <span>Гарантия 15 лет</span>
                    <span>Оплата по факту</span>
                    <span>Договор</span>
                </div>

                <div class="hero__stats">
                    <div>
                        <div class="hero__stat-num" data-countup="500" data-suffix="+">500+</div>
                        <div class="hero__stat-label">Объектов сдано</div>
                    </div>
                    <div>
                        <div class="hero__stat-num" data-countup="15" data-suffix=" лет">15 лет</div>
                        <div class="hero__stat-label">Гарантия</div>
                    </div>
                    <div>
                        <div class="hero__stat-num" data-countup="20" data-suffix="%">20%</div>
                        <div class="hero__stat-label">Экономия на раскрое</div>
                    </div>
                </div>
            </div>

            <div class="hero__visual">
                <?php
                $hero_img = 'https://kvadratyra.ru/wp-content/uploads/2026/02/1771694271426-pp1z3vob0v.jpg';
                ?>
                <img src="<?php echo esc_url($hero_img); ?>" alt="Монтаж кровли — профессиональная бригада" width="400" height="400" loading="eager" decoding="async" fetchpriority="high">
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
<section class="section" id="logistics" style="background:var(--bg-alt);">
    <div class="container">
        <h2 class="section__title animate-in text-center">Почему у нас дешевле и быстрее</h2>
        <p class="section__subtitle animate-in text-center" style="margin-left:auto;margin-right:auto;">Не посредники. Своя логистика, склады и производство.</p>

        <div class="grid grid--3">
            <div class="card animate-in">
                <div class="card__icon"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16H3"></path><path d="M7 16V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v12"></path><circle cx="6" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle><path d="M12 2v14"></path><path d="M15 6h4a2 2 0 0 1 2 2v8"></path></svg></div>
                <h3 class="card__title">Своя ж/д ветка</h3>
                <div class="card__text">Прямые поставки вагонами с заводов Grand Line и Металл Профиль. Минимальная закупочная цена = лучшая цена для вас.</div>
            </div>
            <div class="card animate-in">
                <div class="card__icon"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg></div>
                <h3 class="card__title">Мощный автопарк</h3>
                <div class="card__text">20 машин Hyundai (6 метров) и 5 фур (20 метров). Доставим любой объем точно в срок, не ожидая наемный транспорт.</div>
            </div>
            <div class="card animate-in">
                <div class="card__icon"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4H2v16z"></path><path d="M17 18h1"></path><path d="M13 18h1"></path><path d="M9 18h1"></path></svg></div>
                <h3 class="card__title">Свое производство</h3>
                <div class="card__text">Линии проката профлиста и металлочерепицы. Режем металл в размер вашего дома — никаких переплат за обрезки.</div>
            </div>
            <div class="card animate-in">
                <div class="card__icon"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path></svg></div>
                <h3 class="card__title">4 региона присутствия</h3>
                <div class="card__text">Офисы и склады в Воронежской, Тамбовской, Саратовской и Волгоградской областях. Мы всегда рядом.</div>
            </div>
            <div class="card animate-in">
                <div class="card__icon"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg></div>
                <h3 class="card__title">Экономия 20%</h3>
                <div class="card__text">Делаем раскрой в инженерной программе. Вы платите только за полезную площадь, а не за "воздух" и обрезки.</div>
            </div>
            <div class="card animate-in">
                <div class="card__icon"><svg class="stroke-draw" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Оплата по факту</h3>
                <div class="card__text">Сначала привозим материал или выполняем работу — потом вы платите. Никаких рисков.</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== EXPERT SECTION ===== -->
<section class="section" id="expert">
    <div class="container">
        <div class="grid grid--2 expert-layout">
            <div class="expert-visual">
                <?php
                $expert_photo = 'https://kvadratyra.ru/wp-content/uploads/2026/02/image_1771694471461_j1v9ep.jpg';
                ?>
                <img
                    src="<?php echo esc_url($expert_photo); ?>"
                    alt="Андрей Русских — инженер по кровле и фасадам"
                    width="560"
                    height="560"
                    loading="lazy"
                    decoding="async"
                    style="border-radius:var(--radius);aspect-ratio:1/1;object-fit:cover;"
                >
            </div>
            <div class="expert-content">
                <h2 class="section__title animate-in">Почему я не верю онлайн-калькуляторам</h2>
                <p class="section__subtitle expert-content__subtitle">(и вам не советую)</p>

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
                    <div class="expert-callout">
                        <p>
                            Это экономит вам до 20% бюджета. Вы не покупаете лишний металл, который потом будет валяться в гараже.
                        </p>
                    </div>
                    <p>
                        Поэтому: поиграйте с калькулятором, чтобы узнать порядок цен. А за <strong>точной сметой с экономией</strong> приходите ко мне. Замер бесплатный.
                    </p>
                </div>

                <div class="expert-cta">
                    <a href="#calculator" class="btn btn--primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M9 16l2 2 4-4"></path></svg>
                        Записаться на замер
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== SERVICES ===== -->
<section class="section" id="services" style="background:var(--bg-alt);">
    <div class="container">
        <h2 class="section__title animate-in">Наши услуги</h2>
        <p class="section__subtitle">Полный цикл работ с гарантией результата</p>

        <div class="grid grid--3">
            <a href="<?php echo esc_url(home_url('/krovlya/')); ?>" class="card card--link service-card">
                <div class="service-card__body">
                    <div class="service-card__tag">Кровля</div>
                    <h3 class="card__title">Монтаж и ремонт кровли</h3>
                    <div class="card__text">Металлочерепица, профнастил, мягкая кровля. Стропильная система, утепление, водостоки.</div>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/fasady/')); ?>" class="card card--link service-card">
                <div class="service-card__body">
                    <div class="service-card__tag">Фасады</div>
                    <h3 class="card__title">Отделка фасадов</h3>
                    <div class="card__text">Сайдинг, фасадные панели, штукатурка. Утепление стен (минвата, пеноплекс).</div>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/zabory/')); ?>" class="card card--link service-card">
                <div class="service-card__body">
                    <div class="service-card__tag">Заборы</div>
                    <h3 class="card__title">Установка заборов</h3>
                    <div class="card__text">Профнастил, евроштакетник, 3D-сетка. Ленточный фундамент, кирпичные столбы, ворота.</div>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- ===== IRON GUARANTEES ===== -->
<section class="section" id="guarantees">
    <div class="container">
        <h2 class="section__title animate-in text-center">Железные гарантии</h2>
        <div class="grid grid--3">
            <div class="card">
                <h3 class="card__title" style="color:var(--accent);">15 лет гарантии</h3>
                <div class="card__text">Прописываем в договоре гарантию на материалы и монтажные работы. Мы уверены в качестве.</div>
            </div>
            <div class="card">
                <h3 class="card__title" style="color:var(--accent);">Оплата по факту</h3>
                <div class="card__text">Никаких авансов за "воздух". Привезли материал — вы оплатили. Сделали работу — вы приняли и оплатили.</div>
            </div>
            <div class="card">
                <h3 class="card__title" style="color:var(--accent);">Отвечаем за замер</h3>
                <div class="card__text">Мы замеряем — мы отвечаем. Если инженер ошибся в расчетах, докупаем и привозим материал за свой счет.</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="section" id="how-it-works" style="background:var(--bg-alt);">
    <div class="container">
        <h2 class="section__title animate-in text-center">Как мы работаем</h2>
        <div class="timeline">
            <div class="timeline__item animate-in">
                <div class="timeline__icon"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></div>
                <div class="timeline__content card">
                    <h3>1. Заявка</h3>
                    <p>Оставьте заявку на сайте. Инженер перезвонит в течение 15 минут.</p>
                </div>
            </div>
            <div class="timeline__item animate-in">
                <div class="timeline__icon"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div>
                <div class="timeline__content card">
                    <h3>2. Бесплатный замер</h3>
                    <p>Инженер приезжает, замеряет, показывает образцы материалов вживую.</p>
                </div>
            </div>
            <div class="timeline__item animate-in">
                <div class="timeline__icon"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
                <div class="timeline__content card">
                    <h3>3. Точная смета</h3>
                    <p>Делаем раскрой в программе. Фиксируем цену и сроки в договоре.</p>
                </div>
            </div>
            <div class="timeline__item animate-in">
                <div class="timeline__icon"><svg class="stroke-draw" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
                <div class="timeline__content card">
                    <h3>4. Монтаж и оплата</h3>
                    <p>Привозим материал, монтируем. Вы принимаете работу и оплачиваете по факту.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== ANSWER FIRST ===== -->
<section class="section" id="answer-first">
    <div class="container">
        <h2 class="section__title animate-in text-center">Коротко по делу: что важно перед заказом</h2>
        <div class="grid grid--3">
            <div class="card">
                <h3 class="card__title">Сколько стоит?</h3>
                <div class="card__text">Ориентиры по цене даём сразу, точную смету — после бесплатного замера с раскроем в инженерной программе.</div>
            </div>
            <div class="card">
                <h3 class="card__title">Когда начнёте?</h3>
                <div class="card__text">Обычно 3–7 дней после согласования сметы. Материалы поставляем своим транспортом по 4 областям.</div>
            </div>
            <div class="card">
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
