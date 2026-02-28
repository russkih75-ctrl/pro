<?php
/**
 * Calculator — two modes:
 *   Tab A  "Заказать монтаж"  – multi-step wizard (Profi.ru pattern)
 *   Tab B  "Рассчитать материалы" – 3 sub-tabs: roof / facade / fence
 *
 * Real product names: Grand Line, Металл Профиль (Лобня), Деке (Docke), Технониколь
 * All interactive logic: assets/src/main.js → initQuizCalculator()
 * Lead submit: inc/calc-ajax.php → Telegram Bot API
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;
$phone_raw = function_exists('kv_phone') ? kv_phone(false) : '';
$tg = get_option('kv_telegram', '');
$emoji_base = get_template_directory_uri() . '/assets/img/emoji/';
$calc_context = isset($args['context']) ? (string) $args['context'] : 'default';
$default_service = isset($args['default_service']) ? (string) $args['default_service'] : '';
$is_article_context = $calc_context === 'article';
?>

<section
    class="section section--calculator"
    id="calculator"
    data-calc-context="<?php echo esc_attr($calc_context); ?>"
    data-default-service="<?php echo esc_attr($default_service); ?>"
>
    <div id="quiz"></div>
    <div class="container">

        <h2 class="section__title text-center"><?php echo $is_article_context ? 'Расширенный калькулятор под эту задачу' : 'Калькулятор стоимости'; ?></h2>
        <p class="section__subtitle text-center" style="margin-left:auto;margin-right:auto;">Рассчитайте стоимость монтажа или материалов за 60 секунд. Точная смета — после бесплатного замера.</p>

        <!-- Mode tabs -->
        <div class="calc-tabs" role="tablist">
            <button class="calc-tabs__btn is-active" role="tab" aria-selected="true" data-calc-tab="order">Заказать монтаж</button>
            <button class="calc-tabs__btn" role="tab" aria-selected="false" data-calc-tab="materials">Рассчитать материалы</button>
        </div>

        <!-- ═══ TAB A: Multi-step wizard ═══ -->
        <div class="calc-panel is-active" data-calc-panel="order">
            <div class="card wizard" data-wizard>

                <div class="wizard__progress">
                    <div class="wizard__bar" data-wizard-bar style="width:25%"></div>
                </div>
                <div class="wizard__meta">
                    <div class="wizard__step-label" data-wizard-label>Шаг 1 из 4</div>
                    <div class="wizard__points">Прогресс: <span data-wz-points>0</span> очков</div>
                </div>

                <!-- Step 1: Service -->
                <div class="wizard__step is-active" data-wizard-step="1">
                    <h3 class="wizard__title">Что вам нужно?</h3>
                    <div class="wizard__cards">
                        <label class="wizard__card" data-service="roof">
                            <input type="radio" name="wz_service" value="roof" hidden>
                            <span class="wizard__card-icon" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            </span>
                            <span class="wizard__card-label">Кровля</span>
                        </label>
                        <label class="wizard__card" data-service="facade">
                            <input type="radio" name="wz_service" value="facade" hidden>
                            <span class="wizard__card-icon" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="14.01"></line><line x1="12" y1="14" x2="12" y2="14.01"></line><line x1="8" y1="14" x2="8" y2="14.01"></line><line x1="16" y1="18" x2="16" y2="18.01"></line><line x1="12" y1="18" x2="12" y2="18.01"></line><line x1="8" y1="18" x2="8" y2="18.01"></line></svg>
                            </span>
                            <span class="wizard__card-label">Фасад</span>
                        </label>
                        <label class="wizard__card" data-service="fence">
                            <input type="radio" name="wz_service" value="fence" hidden>
                            <span class="wizard__card-icon" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v7"></path><path d="M20 14v7"></path><rect x="2" y="3" width="20" height="11" rx="2"></rect></svg>
                            </span>
                            <span class="wizard__card-label">Забор</span>
                        </label>
                    </div>
                </div>

                <!-- Step 2: Details -->
                <div class="wizard__step" data-wizard-step="2">
                    <h3 class="wizard__title">Детали проекта</h3>

                    <!-- Roof -->
                    <div class="wizard__fields" data-fields-for="roof" style="display:none">
                        <div class="wizard__field">
                            <label>Покрытие</label>
                            <select data-wz="roof_material">
                                <option value="metallocherepitsa">Металлочерепица (Grand Line, Металл Профиль)</option>
                                <option value="profnastil">Профнастил (С-21, НС-35)</option>
                                <option value="falts">Фальцевая кровля (Кликфальц)</option>
                                <option value="soft">Гибкая черепица (Shinglas, Docke)</option>
                                <option value="composite">Композитная черепица (Премиум)</option>
                                <option value="ceramic">Керамическая / ЦПЧ (Премиум)</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Тип крыши</label>
                            <select data-wz="roof_type">
                                <option value="gable">Двускатная</option>
                                <option value="hip">Четырёхскатная / вальмовая</option>
                                <option value="mansard">Мансардная</option>
                                <option value="flat">Плоская</option>
                                <option value="complex">Сложная многощипцовая</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Площадь крыши, м²</label>
                            <input type="number" data-wz="roof_area" placeholder="120" min="10" max="5000">
                        </div>
                        <div class="wizard__field">
                            <label>Этажность</label>
                            <select data-wz="roof_floors">
                                <option value="1">1 этаж</option>
                                <option value="2">2 этажа</option>
                                <option value="3">3+ этажа</option>
                            </select>
                        </div>
                        <div class="wizard__field" style="grid-column: 1 / -1;">
                            <label>Дополнительные работы</label>
                            <div class="wizard__checkboxes" style="display:flex;gap:15px;flex-wrap:wrap;margin-top:8px;">
                                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" data-wz-addon="roof" value="hydro"> Гидроизоляция</label>
                                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" data-wz-addon="roof" value="insulation"> Утепление (200мм)</label>
                                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" data-wz-addon="roof" value="snow"> Снегозадержатели</label>
                                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" data-wz-addon="roof" value="drain"> Водосток</label>
                                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" data-wz-addon="roof" value="soffit"> Подшивка софитами</label>
                            </div>
                        </div>
                    </div>

                    <!-- Facade -->
                    <div class="wizard__fields" data-fields-for="facade" style="display:none">
                        <div class="wizard__field">
                            <label>Материал отделки</label>
                            <select data-wz="facade_material">
                                <option value="siding_gl">Виниловый сайдинг (Grand Line, Docke)</option>
                                <option value="metal_siding_gl">Металлический сайдинг (Корабельная доска)</option>
                                <option value="panels_gl">Фасадные панели (Я-Фасад, Docke-R)</option>
                                <option value="hauberk_tn">Фасадная плитка (Hauberk)</option>
                                <option value="fibro_cedral">Фиброцементный сайдинг (Cedral)</option>
                                <option value="plaster">Штукатурный фасад (Короед)</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Площадь стен, м²</label>
                            <input type="number" data-wz="facade_area" placeholder="200" min="10" max="5000">
                        </div>
                        <div class="wizard__field">
                            <label>Окна + двери, шт</label>
                            <input type="number" data-wz="facade_openings" placeholder="8" min="0" max="100">
                        </div>
                        <div class="wizard__field">
                            <label>Утепление</label>
                            <select data-wz="facade_insulation">
                                <option value="none">Без утепления</option>
                                <option value="insulation_50">Утепление 50 мм</option>
                                <option value="insulation_100">Утепление 100 мм</option>
                            </select>
                        </div>
                        <div class="wizard__field" style="grid-column: 1 / -1;">
                            <label>Подсистема</label>
                            <select data-wz="facade_subsystem">
                                <option value="wood">Деревянная обрешетка</option>
                                <option value="metal">Металлическая подсистема</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fence -->
                    <div class="wizard__fields" data-fields-for="fence" style="display:none">
                        <div class="wizard__field">
                            <label>Тип забора</label>
                            <select data-wz="fence_type">
                                <option value="profnastil">Профнастил (С-8, МП-20)</option>
                                <option value="shtaketnik">Евроштакетник</option>
                                <option value="mesh_3d">3D-сетка Гиттер</option>
                                <option value="jalousie">Забор-жалюзи</option>
                                <option value="rancho">Забор Ранчо</option>
                                <option value="rabitza">Сетка-рабица</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Длина забора, пог.м</label>
                            <input type="number" data-wz="fence_length" placeholder="60" min="1" max="5000">
                        </div>
                        <div class="wizard__field">
                            <label>Высота, м</label>
                            <select data-wz="fence_height">
                                <option value="1.5">1,5 м</option>
                                <option value="1.8">1,8 м</option>
                                <option value="2.0" selected>2,0 м</option>
                                <option value="2.5">2,5 м</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Ворота / калитки</label>
                            <select data-wz="fence_gates">
                                <option value="none">Не нужны</option>
                                <option value="swing">Распашные ворота + калитка</option>
                                <option value="sliding">Откатные ворота (с автоматикой) + калитка</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Price estimate -->
                <div class="wizard__step" data-wizard-step="3">
                    <h3 class="wizard__title">Предварительная стоимость</h3>
                    <div class="wizard__estimate">
                        <div class="wizard__price-wrap">
                            <span class="wizard__price-label">Ориентировочно:</span>
                            <span class="wizard__price" data-wz-price>—</span>
                            <span class="wizard__price-currency">₽</span>
                        </div>
                        <div class="wizard__includes">
                            <p style="color:var(--text-secondary);margin-bottom:8px;">Что включено:</p>
                            <div data-wz-includes></div>
                        </div>
                        
                        <div class="wizard__packages" data-wz-packages></div>

                        <!-- NEW: Disclaimer Block -->
                        <div class="wizard__disclaimer" style="margin-top:24px; padding:16px; background:var(--bg); border-radius:12px; border:1px solid var(--border);">
                            <div style="display:flex; gap:16px; align-items:flex-start;">
                                <img src="<?php echo esc_url('https://kvadratyra.ru/wp-content/uploads/2026/02/image_1771525065585_tn33pe.jpg'); ?>" alt="Инженер Андрей Русских" width="60" height="60" style="border-radius:50%; object-fit:cover; flex-shrink:0;">
                                <div>
                                    <p style="font-size:14px; color:var(--accent); font-weight:600; margin-bottom:4px;">Андрей Русских, ведущий инженер</p>
                                    <p style="font-size:13px; color:var(--text-secondary); line-height:1.5;">
                                        Калькулятор дает лишь ориентир. На практике я делаю <strong>бесплатную раскладку листов в программе «Кровля Профи» или SketchUp</strong>. Это позволяет учесть каждый нахлест, окна, ендовы и <strong>сэкономить вам до 20% бюджета</strong> за счет минимизации обрезков.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Step 4: Contact -->
                <div class="wizard__step" data-wizard-step="4">
                    <h3 class="wizard__title">Получить точную смету с раскладкой</h3>
                    <p style="font-size:14px; color:var(--text-secondary); margin-bottom:16px;">Я получу ваши данные из калькулятора, сделаю 3D-модель в SketchUp или раскладку в «Кровля Профи» и пришлю вам 2-3 варианта сметы с максимальной экономией.</p>
                    <div class="wizard__fields">
                        <div class="wizard__field">
                            <label>Город / район</label>
                            <input type="text" data-wz="city" placeholder="Борисоглебск">
                        </div>
                        <div class="wizard__field">
                            <label>Комментарий (необязательно)</label>
                            <input type="text" data-wz="comment" placeholder="Что нужно сделать, особенности объекта">
                        </div>
                        <label class="wizard__consent" style="grid-column:1 / -1;">
                            <input type="checkbox" data-wz="consent">
                            <span>Согласен на обработку персональных данных и с <a href="/politika-konfidencialnosti/" target="_blank" rel="noopener">политикой конфиденциальности</a>.</span>
                        </label>
                    </div>
                    <div class="wizard__success" data-wz-success style="display:none">
                        <div class="wizard__success-icon">✓</div>
                        <h3>Telegram открыт</h3>
                        <p style="color:var(--text-secondary)">Отправьте сообщение — инженер ответит в чате и уточнит детали.</p>
                    </div>
                </div>

                <!-- Nav -->
                <div class="wizard__nav">
                    <button class="btn" data-wz-prev style="display:none"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-3px;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg> Назад</button>
                    <button class="btn btn--primary" data-wz-next>Далее <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:4px;vertical-align:-3px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></button>
                    <button class="btn btn--primary" data-wz-submit style="display:none"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><polyline points="20 6 9 17 4 12"></polyline></svg> Запросить точный расчёт</button>
                </div>
            </div>
        </div>

        <!-- ═══ TAB B: Material calculators ═══ -->
        <div class="calc-panel" data-calc-panel="materials">
            <div class="card" style="padding:40px">

                <div class="calc-subtabs" role="tablist">
                    <button class="calc-subtabs__btn is-active" role="tab" data-mat-tab="m-roof">Кровля</button>
                    <button class="calc-subtabs__btn" role="tab" data-mat-tab="m-facade">Фасад</button>
                    <button class="calc-subtabs__btn" role="tab" data-mat-tab="m-fence">Забор</button>
                </div>

                <!-- Roof calc -->
                <div class="mat-panel is-active" data-mat-panel="m-roof">
                    <h3>Расчёт кровельных материалов</h3>
                    <div class="grid grid--2" style="gap:20px">
                        <div class="wizard__field">
                            <label>Покрытие</label>
                            <select data-mc="r_material">
                                <option value="metallocherepitsa_gl">Grand Line Металлочерепица</option>
                                <option value="metallocherepitsa_mp">Металл Профиль Монтеррей</option>
                                <option value="profnastil_gl">Grand Line Профнастил</option>
                                <option value="profnastil_mp">Металл Профиль Профнастил</option>
                                <option value="soft_shinglas">Технониколь Шинглас</option>
                                <option value="soft_docke">Docke Гибкая черепица</option>
                                <option value="falts_gl">Grand Line Кликфальц</option>
                                <option value="composite">Композитная черепица Luxard</option>
                                <option value="ceramic">Керамическая черепица Braas</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Тип крыши</label>
                            <select data-mc="r_type">
                                <option value="gable" data-coef="1.0">Двускатная</option>
                                <option value="hip" data-coef="1.15">Четырёхскатная</option>
                                <option value="mansard" data-coef="1.25">Мансардная</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Длина ската, м</label>
                            <input type="number" data-mc="r_length" placeholder="12" min="1" max="100">
                        </div>
                        <div class="wizard__field">
                            <label>Ширина ската, м</label>
                            <input type="number" data-mc="r_width" placeholder="6" min="1" max="100">
                        </div>
                    </div>
                    <button class="btn btn--primary" style="margin-top:24px" data-mc-calc="roof">Рассчитать</button>
                    <div class="spec-table" data-mc-result="roof" style="display:none"></div>
                    
                    <!-- NEW: Disclaimer Block for Materials -->
                    <div class="wizard__disclaimer" style="margin-top:24px; padding:16px; background:var(--bg); border-radius:12px; border:1px solid var(--border);">
                         <p style="font-size:13px; color:var(--text-secondary); line-height:1.5;">
                            Это "грязный" расчет площади. В реальности листы имеют полезную и полную ширину. 
                            <strong>Мы сделаем раскладку в программе бесплатно</strong> — это сэкономит вам до 20% на обрезках.
                        </p>
                    </div>

                    <div class="spec-cta" data-mc-cta style="display:none">
                        <?php if ($tg): ?>
                        <a href="<?php echo esc_url($tg); ?>" class="btn btn--primary" target="_blank" rel="noopener">Написать в Telegram</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Facade calc -->
                <div class="mat-panel" data-mat-panel="m-facade">
                    <h3>Расчёт фасадных материалов</h3>
                    <div class="grid grid--2" style="gap:20px">
                        <div class="wizard__field">
                            <label>Материал</label>
                            <select data-mc="f_material">
                                <option value="siding_gl">Grand Line Виниловый сайдинг</option>
                                <option value="siding_docke">Docke Premium Сайдинг</option>
                                <option value="metal_siding_gl">Grand Line Металлосайдинг</option>
                                <option value="metal_siding_mp">Металл Профиль Металлосайдинг</option>
                                <option value="panels_gl">Grand Line Я-Фасад</option>
                                <option value="panels_docke">Docke-R Панели Stern</option>
                                <option value="hauberk_tn">Технониколь Hauberk</option>
                                <option value="fibro_cedral">Фиброцементный сайдинг Cedral</option>
                                <option value="plaster">Штукатурный фасад (Короед)</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Площадь стен, м²</label>
                            <input type="number" data-mc="f_area" placeholder="200" min="1" max="5000">
                        </div>
                        <div class="wizard__field">
                            <label>Окна + двери, шт</label>
                            <input type="number" data-mc="f_openings" placeholder="8" min="0" max="100" value="8">
                        </div>
                        <div class="wizard__field">
                            <label>Утепление</label>
                            <select data-mc="f_insulation">
                                <option value="none">Без утепления</option>
                                <option value="insulation_50">Утепление 50 мм</option>
                                <option value="insulation_100">Утепление 100 мм</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn--primary" style="margin-top:24px" data-mc-calc="facade">Рассчитать</button>
                    <div class="spec-table" data-mc-result="facade" style="display:none"></div>
                    
                    <div class="wizard__disclaimer" style="margin-top:24px; padding:16px; background:var(--bg); border-radius:12px; border:1px solid var(--border);">
                         <p style="font-size:13px; color:var(--text-secondary); line-height:1.5;">
                            Программа автоматически вычтет окна и двери, но добавит нахлесты. 
                            <strong>Точный расчет экономит до 15% сайдинга.</strong> Закажите бесплатный расчет.
                        </p>
                    </div>

                    <div class="spec-cta" data-mc-cta style="display:none">
                        <?php if ($tg): ?>
                        <a href="<?php echo esc_url($tg); ?>" class="btn btn--primary" target="_blank" rel="noopener">Написать в Telegram</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fence calc -->
                <div class="mat-panel" data-mat-panel="m-fence">
                    <h3>Расчёт материалов для забора</h3>
                    <div class="grid grid--2" style="gap:20px">
                        <div class="wizard__field">
                            <label>Тип забора</label>
                            <select data-mc="z_type">
                                <option value="profnastil_gl">Grand Line Профнастил</option>
                                <option value="profnastil_mp">Металл Профиль Профнастил</option>
                                <option value="shtaketnik_gl">Grand Line Евроштакетник</option>
                                <option value="shtaketnik_mp">Металл Профиль Евроштакетник</option>
                                <option value="mesh_3d">3D-сетка Гиттер</option>
                                <option value="rabitza">Сетка-рабица</option>
                                <option value="jalousie">Забор-жалюзи</option>
                                <option value="rancho">Забор Ранчо</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Длина забора, пог.м</label>
                            <input type="number" data-mc="z_length" placeholder="60" min="1" max="5000">
                        </div>
                        <div class="wizard__field">
                            <label>Высота, м</label>
                            <select data-mc="z_height">
                                <option value="1.5">1,5 м</option>
                                <option value="1.8">1,8 м</option>
                                <option value="2.0" selected>2,0 м</option>
                                <option value="2.5">2,5 м</option>
                            </select>
                        </div>
                        <div class="wizard__field">
                            <label>Ворота + калитки, шт</label>
                            <input type="number" data-mc="z_gates" placeholder="1" min="0" max="20" value="1">
                        </div>
                    </div>
                    <button class="btn btn--primary" style="margin-top:24px" data-mc-calc="fence">Рассчитать</button>
                    <div class="spec-table" data-mc-result="fence" style="display:none"></div>
                    <div class="spec-cta" data-mc-cta style="display:none">
                        <?php if ($tg): ?>
                        <a href="<?php echo esc_url($tg); ?>" class="btn btn--primary" target="_blank" rel="noopener">Написать в Telegram</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- Calculator FAQ (SEO) -->
        <div class="calc-faq" style="margin-top:48px">
            <h3>Частые вопросы о расчёте</h3>
            <div class="faq-item animate-in">
                <button class="faq-q" data-faq-toggle aria-expanded="false">Насколько точен онлайн-расчёт?</button>
                <div class="faq-a">
                    <p>Онлайн-калькулятор даёт ориентировочную стоимость ±15%. Точную смету составляет инженер после бесплатного замера — с учётом сложных узлов и особенностей конструкции.</p>
                </div>
            </div>
            <div class="faq-item animate-in">
                <button class="faq-q" data-faq-toggle aria-expanded="false">Какие бренды вы используете?</button>
                <div class="faq-a">
                    <p>Работаем напрямую с Grand Line, Металл Профиль (Лобня), Docke и Технониколь. Это ведущие производители кровельных и фасадных материалов в России. Прямые поставки — без наценок посредников.</p>
                </div>
            </div>
            <div class="faq-item animate-in">
                <button class="faq-q" data-faq-toggle aria-expanded="false">Зачем запас 10% при расчёте?</button>
                <div class="faq-a">
                    <p>Запас 10% закладывается на подрезку, нахлёсты и возможный брак. Grand Line и Металл Профиль рекомендуют 10–15% к расчётной площади.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Sticky Engineer Widget -->
    <a href="#calculator" class="sticky-engineer-widget" data-wz-next style="text-decoration:none;">
        <div class="sticky-engineer-widget__avatar">
            <img src="<?php echo esc_url('https://kvadratyra.ru/wp-content/uploads/2026/02/image_1771525065585_tn33pe.jpg'); ?>" alt="Андрей Русских" width="48" height="48" loading="lazy">
            <span class="sticky-engineer-widget__badge"></span>
        </div>
        <div class="sticky-engineer-widget__text">
            <strong>Инженер Андрей (20 лет опыта)</strong>
            <span>Спросить или получить точную смету</span>
        </div>
    </a>
</section>
