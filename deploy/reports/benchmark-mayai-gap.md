# Benchmark vs mayai.ru

Reference: [mayai.ru](https://mayai.ru/)

## Gap Matrix

| Area | mayai.ru baseline | Our project status | Gap | Priority |
|---|---|---|---|---|
| Information architecture |  |  |  |  |
| First screen clarity |  |  |  |  |
| Navigation and hubs |  |  |  |  |
| Schema/entity stack |  |  |  |  |
| GEO answer-first structure |  |  |  |  |
| Technical SEO (robots/sitemaps) |  |  |  |  |
| CWV and media loading |  |  |  |  |

## Source Notes

# Аудит донора: mayai.ru — стек, тема, SEO/GEO-архитектура (2026)

Источник для разбора: [mayai.ru](https://mayai.ru/)

## 1) Технический стек (что именно крутится «под капотом»)

- **CMS**: WordPress (**6.9**).  
  Признаки: открытый REST (`/wp-json/`), стандартные `wp-includes/*`, `wp-content/*`, и параметры CMS в инициализации Яндекс.Метрики.

- **Тема**: кастомная WordPress-тема **`kov4eg-mcp-theme`** (читается из путей ассетов).  
  Хедер темы (`/wp-content/themes/kov4eg-mcp-theme/style.css`) сообщает:
  - **Theme Name**: `Kov4eg`
  - **Author**: `Kov4eg` (`https://kv-ai.ru`)
  - **Version**: `3.9.15`
  - **Сборка**: **Vite** (“compiled via Vite”)
  - Позиционирование: “MCP Ready”, “Dark Mode”

- **Сборка фронта**:
  - CSS: `.../assets/dist/style.css?ver=3.9.15`
  - JS: `.../assets/dist/main.js?ver=3.9.15`
  - Шрифты: Google Fonts (`Inter`)
  - Есть `speculationrules` (prefetch) — ускорение переходов в Chrome.

- **JS-библиотеки и интерактив** (по бандлу темы):
  - **Lottie (lottie-web)** — анимации “уток” грузятся JSON’ами через `data-lottie-path`.
  - Собственные модули темы: мобильное меню, слайдер отзывов (prev/next + dots), lightbox для скриншотов, генерация/подсветка активных состояний.

- **Плагины (точно видно по ассетам)**:
  - **`wp-yandex-metrika`** — скрипты `YmEc.min.js`, `frontend.min.js`, + подключение `mc.yandex.ru/metrika/tag.js`.
  - **`ewww-image-optimizer`** — lazyload через `lazysizes.min.js`, картинки отдаются с `data-src`, `data-srcset`.

## 2) SEO/GEO-архитектура: «тройная» структура, как это сделано

Если разложить mayai.ru “как систему”, у него реально 3 слоя:

### Слой A — техническая база индексации и качества (Tech SEO)

- **`robots.txt`**:
  - закрыт `/wp-admin/`, `/wp-includes/`, поиск `/?s=` и `/search/`
  - задан `Crawl-delay` для Ahrefs/Semrush
  - подключены **2 sitemap**: `wp-sitemap.xml` и `sitemap.xml` (по факту оба ведут на WP sitemap index)

- **Sitemaps**:
  - WP sitemap index включает:
    - `wp-sitemap-posts-post-{1..3}.xml` (посты)
    - `wp-sitemap-posts-page-1.xml` (страницы)
    - `wp-sitemap-taxonomies-category-1.xml` (категории)
    - `wp-sitemap-taxonomies-post_tag-1.xml` (теги)

- **Производительность и UX**:
  - preload CSS/шрифтов, lazyload изображений, prefetch правилом `speculationrules`.
  - Это “белый” фундамент под Core Web Vitals и поведенческие метрики.

### Слой B — инфоструктура и внутренние переходы (Information Architecture)

- **Главная** — не просто “витрина”, а **лендинг-агрегатор**:
  - секции: Hero → Features (“bento”) → How it works (“zigzag”) → For who → FAQ → Resources → Reviews → Social proof → Blog cards → Footer
  - внизу — подборка свежих постов (карточки с категорией/датой/временем чтения/эксерптом)

- **Статьи (посты) построены как SEO-страницы**:
  - **Оглавление** (TOC) в aside (якоря на H2/H3)
  - Блоки вовлечения: “мини‑квест/квиз”, “ключевые ориентиры”
  - В конце: **FAQ**, **Related posts**, **Prev/Next**, CTA “что делать дальше”
  - Быстрые ссылки (навигационные шорткаты) в начале

- **Таксономии**:
  - В sitemap категорий видно как минимум `https://mayai.ru/category/make/`
  - По посту видно “make.com” как рубрика/секция.

### Слой C — GEO (оптимизация под AI-ответы) через структуру + Schema

Ключевая фишка mayai.ru — не “ключи”, а **структурированная сущностная подача** и микроразметка:

- **JSON-LD @graph** на главной:
  - `Organization` + `sameAs` (Telegram/VK/YouTube/MAX) — entity-склейка
  - `Person` (инструктор) + `knowsAbout` — тематический профиль
  - `WebSite` + `SearchAction` — правильный сигнал сайта
  - `Course` + `Offer` + `AggregateRating` + `Review[]` — богатый сниппет “продукта”
  - `FAQPage` — rich snippets
  - `WebPage` + `speakable` — сигнал для ассистентов/озвучки (и вообще для “answer engines”)

- **JSON-LD на статьях**:
  - `Article` + `speakable` (селекторы `.entry-title`, `.entry-content`)
  - Важно: в примере статьи `author.name` пустой — это место, где можно усилить E‑E‑A‑T.

## 3) Что важно из ваших презентаций (и как это отражено у донора)

Из `presentation_yandex_search_algorithms_2026.html` и `presentation_google_search_algorithms_2026.html` (берём только “белое/GEO”):

- **Структура “Вопрос → короткий ответ”** + списки/таблицы → у mayai.ru это реализовано через:
  - FAQ на главной (и FAQ-блок в статьях)
  - TOC + явные H2/H3, сканируемые блоки

- **Entity / E‑E‑A‑T** → у mayai.ru:
  - `Organization/Person` в Schema + `sameAs`
  - Course schema с рейтингом/отзывами (социальное доказательство)

- **GEO / AI цитируемость** → у mayai.ru:
  - высокая “плотность фактов” в лидах (определения, цифры, конкретика)
  - speakable-селекторы (под ассистентов)

## 4) Наблюдения/риски (то, что стоит исправить при копировании)

- **Ссылка на блог `/blog/` ведёт в 404**: в футере “Блог” кликается, но страницы нет. Это технический SEO-дефект (внутренние ссылки на 404).
- **Очень большой объём постов (≈2000 URL только в `wp-sitemap-posts-post-1.xml`)** и часть с “обрубленными” слагами выглядит как programmatic/AI‑массовка — риск под “helpful content” фильтры.
- **Author пустой в Article schema** — минус для доверия в Google/E‑E‑A‑T.

## 5) Как воспроизвести такую реализацию у себя (без “чёрных” схем)

### Минимальный “точно такой же” стек

- WordPress (актуальная версия)
- Кастомная тема (или форк) с:
  - Vite-сборкой (`assets/src` → `assets/dist`)
  - Lottie-анимациями (lottie-web)
  - TOC для статей
  - FAQ на главной + (опционально) FAQ schema на статьях
  - Reviews slider + lightbox
  - speculationrules (prefetch)
- Плагины:
  - Яндекс.Метрика (или вставка кода вручную)
  - EWWW (или любой нормальный image optimizer + lazyload)

### SEO/GEO-шаблоны контента (как “архитектура”)

- **Главная**: `Course + Offer + AggregateRating + Review + FAQPage + Organization/Person + speakable`
- **Статья**:
  - лид-абзац с определением (1–2 предложения)
  - TOC
  - H2/H3 блоки с конкретикой, списки/таблицы
  - FAQ в конце
  - Related + prev/next + CTA
  - `Article` schema + заполненный `author` (и отдельная страница автора)

### Где “ИИ” в этой системе (практически)

- **ИИ как производство контента**: генерация черновиков + редактура (повышать fact density, добавлять свои таблицы/цифры).
- **ИИ как автоматизация**: связка Make.com + публикация в WP + постинг в соцсети.
- **ИИ как GEO**: структура ответа + schema + entity-профили (sameAs).

