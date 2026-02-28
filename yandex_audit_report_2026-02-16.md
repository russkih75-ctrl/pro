# Аудит kvadratyra.ru по Yandex Bible 2026 (white SEO + безопасные улучшения)

Дата: 2026-02-16

Фокус: L1 (индексация/краулинг/дубли) → L2 (коммерческие факторы/траст) → L3 (YATI/Нейро‑готовность) + GEO(AI) кластер.

## 0) Короткий вывод

- **Техническая база сильная**: виртуальные GEO‑страницы с 200/404‑гигиеной, анти‑thin скоринг, GEO‑sitemap, IndexNow, базовая схема/FAQ/HowTo, TOC для контента.
- **Главные риски (P0/P1)**: canonical/OG для виртуальных GEO, политика `noindex` (лучше `follow`), дублирование/несвязность Schema (FAQ + AggregateRating + Organization), “источник истины” для `robots.txt` (в проде может быть статический файл).

## 1) Что было в Вебмастере (факт фиксации)

- **Ошибка (исторически)**: «Главная страница сайта недоступна для робота (500 Internal Server Error)».
  - Текущее состояние по внешней проверке: **200 OK**.
- **Рекомендации (видимые в чек‑листе)**:
  - **Description отсутствует** → закрыто fallback‑метой в теме (`wp-content/themes/kvadratyra-theme/functions.php`).
  - **Яндекс Бизнес** → карточка есть (есть ссылка в `sameAs` в schema‑графе), важно держать заполнение актуальным.
  - **favicon** → закрыто по доступности (перехват `/favicon.ico`), но бренд‑иконку стоит заменить.
  - **robots.txt не найден** → закрыто статическим `robots.txt` на сервере (см. `automation/robots.txt` как эталон).

## 2) Что уже сделано (и что видно в коде)

### 2.1 robots.txt / Clean-param / disallow

- В MU‑плагине есть добавление `Clean-param` и Disallow для поиска через фильтр `robots_txt` (`wp-content/mu-plugins/kv-geo/lib/sitemaps.php`).
- При этом в проекте присутствует **эталон статического robots** (`automation/robots.txt`) — это важно: если nginx отдаёт `robots.txt` как файл, WordPress‑фильтр не влияет.

### 2.2 GEO‑sitemaps

- GEO sitemap index + 2 саб‑sitemap реализованы (`wp-content/mu-plugins/kv-geo/lib/sitemaps.php`):
  - `/kv-geo-sitemap.xml` → index
  - `/kv-geo-sitemap-cities.xml` → `/geo/` + города
  - `/kv-geo-sitemap-services.xml` → city/service + city/service/intent
- Текущий объём по данным JSON: **31 город × 3 услуги × 6 интентов = 651 URL** → лимит `maxUrls=5000` сейчас не режет страницы.

### 2.3 GEO‑hygiene (soft‑404, thin guard, noindex)

- Виртуальные GEO‑URL отдаются 200 и валидируются; невалидные service/intent дают 404 (`wp-content/mu-plugins/kv-geo/lib/routing.php`).
- Anti‑thin скоринг города и noindex ниже порога (`wp-content/mu-plugins/kv-geo/lib/validators.php` + meta robots в `routing.php`).

### 2.4 IndexNow

- Очередь, дедуп, cron‑отправка и key‑file по rewrite реализованы (`wp-content/mu-plugins/kv-geo/lib/indexnow.php`).

## 3) Таблица аудита (требование → статус → где в коде → риск → что сделать)

### L1 — Индексация / краулинг / дубли

| Требование (Bible) | Статус | Где в коде/конфиге | Риск | Что сделать |
|---|---|---|---|---|
| Robots: disallow системных путей, поиск, параметры | частично | `automation/robots.txt` (эталон) + `kv-geo/lib/sitemaps.php` (filter) | рассинхрон “файл vs WP‑фильтр” | выбрать **один** источник истины: (а) статический robots на сервере, (б) WP robots. Если оставляем оба — синхронизировать директивы и явно документировать |
| Clean-param для рекламных параметров | ок | `automation/robots.txt` (utm, fbclid/gclid/yclid) | мусорные дубли URL | держать список параметров актуальным (добавить при необходимости `utm_id`, `yclid`, `openstat`, `from`, `ref`, и т.п.) |
| Sitemap coverage для GEO | ок | `kv-geo/lib/sitemaps.php` | потеря индексации при росте кластера | при расширении ядра: добавить пагинацию саб‑sitemap (index → cities‑1..N, services‑1..N) вместо одного файла |
| Неиндексируемые GEO не попадают в sitemap | ок | `kv-geo/lib/sitemaps.php` + `kv_geo_city_is_indexable()` | попадание thin в индекс | дополнительно рассмотреть “indexable per service” (если в городе нет цен/данных по услуге) |
| 404/soft‑404 гигиена для виртуальных URL | ок | `kv-geo/lib/routing.php` | soft‑404 снижает качество сайта | оставить как есть; добавить автотесты/мониторинг 404‑паттернов по логам/Вебмастеру |
| Canonical + og:url для виртуальных GEO | не закрыто явно | явной логики в теме/плагине не видно | дубли (слеш/без слеша, параметры), слабый “сигнал основной” | добавить canonical и `og:url` для GEO в `kv-geo/lib/routing.php` (wp_head) |
| Политика noindex для thin | частично | `kv-geo/lib/routing.php` → `noindex,nofollow` | `nofollow` режет краулинг/передачу веса | заменить на `noindex,follow` (как минимум), чтобы поисковик продолжал ходить по ссылкам |

### L2 — Коммерческие факторы / траст

| Требование (Bible) | Статус | Где | Риск | Что сделать |
|---|---|---|---|---|
| NAP: телефон/почта/адрес/режим | частично | опции темы + вывод в `footer.php`, страницы в `inc/trust-pages.php` | недоверие/слабые коммерческие сигналы | заполнить реальные данные (адрес, часы, юр.данные), синхронизировать: футер ↔ schema ↔ страницы доверия |
| Реквизиты (ИНН/ОГРН) | частично | `inc/trust-pages.php`, `footer.php`, `inc/schema-graph.php` | коммерческий сайт без реквизитов = минус доверие | заполнить в настройках темы и проверить вывод на `/rekvizity/` + в футере |
| Страницы доверия (контакты/доставка/оплата/гарантия/политики) | ок (создание) / частично (качество) | `inc/trust-pages.php` | “генерик” контент может быть слабым | усилить контент: сроки, география, условия гарантии, документы, фото команды/офиса, карта |
| Отзывы/соцдоказательства | ок (есть блок) | `template-parts/reviews-slider.php` | “нереальные” отзывы без источника | добавить источники: ссылка на карточку в Яндекс Бизнес, фото объектов, кейсы с цифрами; опционально Review schema на реальные страницы |
| Schema Organization/LocalBusiness с NAP | частично | `inc/schema-graph.php`, GEO schema в `kv-geo/lib/routing.php` | несвязная/дублирующаяся schema снижает полезность | связать сущности через `@id` и убрать дублирование типов (см. P0 ниже) |

### L3 — YATI/Нейро‑готовность (контент, Q/A, структура)

| Требование (Bible) | Статус | Где | Риск | Что сделать |
|---|---|---|---|---|
| Answer‑first (короткий ответ сразу) | ок | intent `qa_templates` (`intents.json`) + quick answer в `kvadratyra-theme/kv-geo.php` + FAQ JSON‑LD в `kv-geo/lib/routing.php` | слабая нейро‑релевантность без “сразу по делу” | держать ответы 200–400 знаков, добавлять факты/цифры/условия |
| Структура H1→H2→H3, “сканируемость” | ок | шаблоны темы + TOC `inc/toc.php` | хаос заголовков = хуже понимание документа | убедиться, что нет “прыжков” и повторяющихся H2‑шаблонов на одной странице GEO |
| Таблицы/сравнения (материалы/цены) | ок | `services.json` + таблицы в `kv-geo.php` | thin без структурированных блоков | поддерживать таблицы актуальными и с понятными единицами измерения |
| PAA/FAQ из семантики (реальные запросы) | ок | `kv-geo/lib/semantic.php` + PAA блоки в `kv-geo.php` + FAQ JSON‑LD в `routing.php` | если много дублей FAQPage — может быть шум | унифицировать FAQ schema: **один** главный FAQPage JSON‑LD на страницу, без “повторов” |
| Schema: FAQ/HowTo/Article | ок/частично | `schema-graph.php`, `schema-article.php`, GEO schema в `routing.php` | конфликтующие schema, дубли типов | связать через `@id`, убрать дублирование FAQ/Rating (см. P0) |

### GEO(AI) — кластер/перелинковка/анти-thin

| Требование | Статус | Где | Риск | Что сделать |
|---|---|---|---|---|
| Hub → City → Service → Intent перелинковка | ок | `geo-hub.php`, `kv-geo.php`, “Следующий шаг”, nearby links | разрыв кластера | на `/geo/` улучшить ссылки “Услуги” (сейчас ведут на `/#services`) — лучше вести на реальные хабы/лендинги |
| Анти‑thin guard | ок | `validators.php` + `routing.php` noindex + sitemap exclude | thin‑фильтры | добавить per‑service guard (не индексировать услугу в городе, если нет локальных данных/цены) |
| Семантические блоки по области (Wordstat core) | ок | `semantic-core.json` + `semantic.php` | устаревание семантики | обновлять ядро раз в \(N\) месяцев, расширять intent_mapping и стадии |

## 4) Бэклог правок (P0/P1/P2) — с привязкой к файлам

### P0 (критично)

1) **Canonical/og:url для виртуальных GEO**  ✅ внедрено
   - Файл: `wp-content/mu-plugins/kv-geo/lib/routing.php`  
   - Зачем: уменьшить риск дублей по параметрам/слешам и усилить “основной URL”.

2) **`noindex,follow` вместо `noindex,nofollow` для thin GEO**  ✅ внедрено
   - Файл: `wp-content/mu-plugins/kv-geo/lib/routing.php`  
   - Зачем: не резать обход и вес внутренних ссылок.

3) **Унификация Schema (убрать дубли/связать сущности через `@id`)**  ✅ частично внедрено
   - Файлы:  
     - `wp-content/themes/kvadratyra-theme/inc/schema-graph.php`  
     - `wp-content/themes/kvadratyra-theme/template-parts/reviews-slider.php`  
     - `wp-content/themes/kvadratyra-theme/template-parts/faq-block.php`  
   - Зачем: сейчас на главной потенциально несколько FAQPage и несколько HomeAndConstructionBusiness без общей связи — лучше сделать одну согласованную graph‑структуру.
   - Статус: убран FAQPage из homepage graph; AggregateRating привязан к `#organization`.

### P1 (важно)

4) **Per‑service indexability для sitemap/индекса** (если нет локальных цен/данных по услуге — не индексировать service/intent страницы в этом городе)  
   - Файлы: `wp-content/mu-plugins/kv-geo/lib/sitemaps.php`, возможно `validators.php`/`routing.php`

5) **Синхронизация/документация robots.txt** (статический файл vs WP filter)  
   - Файл‑эталон: `automation/robots.txt`  
   - Код: `wp-content/mu-plugins/kv-geo/lib/sitemaps.php`

6) **Улучшить hub `/geo/`: “Услуги” должны вести на полезные страницы**  
   - Файл: `wp-content/themes/kvadratyra-theme/geo-hub.php`

### P2 (рост)

7) **Усиление коммерческих факторов** (адрес/карта/фото команды/кейсы с цифрами/сертификаты)  
   - Файлы: `inc/trust-pages.php`, шаблоны страниц, контент

8) **Метрика/Вебмастер: связать данные с бэклогом**  
   - Метрика: проверить цели/события (`phone_click`, `tg_click`, `cta_header_call`, `calc_submit`, `faq_open`, `view_*`)  
   - Вебмастер: переснять “Качество страниц / Индексация / Исключения / CTR” после переобхода

## 5) Что нужно снять в Метрике/Вебмастере (чтобы зафиксировать эффект)

- Метрика: конверсии по целям, источники, отказы/время, скролл по GEO, Webvisor‑паттерны по калькулятору/CTA.
- Вебмастер: исключённые/дубли, качество страниц, проблемы сканирования, динамика CTR по GEO‑страницам.

