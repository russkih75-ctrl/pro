# Деплой (WordPress) — kvadratyra.ru

Ниже — безопасный “ручной” деплой без git: **заливаем только изменённые файлы** в `wp-content/`.

## 0) Перед деплоем (обязательно)

- **Сделайте бэкап**:
  - файлов `wp-content/themes/kvadratyra-theme/` и `wp-content/mu-plugins/kv-geo/`
  - базы (любой дамп в панели хостинга)
- Если есть кэш-плагины / Cloudflare — приготовьтесь **очистить кэш** после загрузки.

## 1) Что именно заливать на сервер

### Тема: `wp-content/themes/kvadratyra-theme/`

- `header.php` (preconnect/dns-prefetch)
- `functions.php` (CWV: defer, non-blocking fonts, lazy attrs, dequeue wp styles)
- `front-page.php` (LSI/факты в Features)
- `single.php` (убран “одинаковый FAQ” из single)
- `kv-geo.php` (GEO-шаблон + блок партнёрских точек, рандомный интро)
- `footer.php` (глобальный `<dialog>` для записи в партнёрскую точку)
- `style.css` (стили .kv-dialog, .kv-partner-point)
- `assets/dist/main.js` (последняя сборка Vite, в т.ч. initPartnerPointsBooking)
- `assets/dist/style.css` (последняя сборка Vite)

### MU‑plugin: `wp-content/mu-plugins/kv-geo/`

- `lib/data.php` (функции партнёров и точек: kv_geo_get_partner_points_for_city и др.)
- `lib/routing.php` (Schema: HQ-контакты, офис только при type=own)
- `lib/indexnow.php` (добавлен `guide` в авто‑enqueue)
- `data/partners.json` (справочник партнёров, «Сталь Сервис»)
- `data/partner-points.json` (точки по городам: Борисоглебск, Анна, Бобров и др.)
- `data/cities.json` (offices пустые, чтобы не подставлять партнёров в Schema)

> Важно: **не нужно** заливать `node_modules/`, `assets/src/`, dev‑файлы сборки — только `assets/dist/*` + PHP‑файлы.

## 2) Как заливать (любой способ)

- **FTP/SFTP** (FileZilla/WinSCP) — просто перезапишите файлы по тем же путям.
- **Панель хостинга** (файловый менеджер) — аналогично.

## 3) После деплоя (чек-лист 5 минут)

### 3.1 Сброс кэшей

- Очистить кэш плагина (если есть)
- Очистить серверный кэш/Cloudflare (если включены)

### 3.2 Перманентные ссылки

В админке WP: **Настройки → Постоянные ссылки → Сохранить** (без изменений) — на всякий случай.

### 3.3 Быстрые проверки

- **Главная**: шрифты грузятся нормально, нет “прыжков” верстки.
- **GEO‑страницы**: нет вложенного `<main>` (валидная структура), хлебные крошки в JSON‑LD есть.
- **Метрика**: клики `tel:` и `t.me` отправляют цели `phone_click` и `tg_click`.
- **IndexNow**: при публикации `guide` URL попадает в очередь (страница “IndexNow” в админке).
- **Партнёрские точки**: на GEO-странице (напр. Борисоглебск) есть блок «Оплата офлайн», кнопка «Записаться на визит» открывает модал, после отправки — кнопка «Открыть точку партнёра» без автоперехода.

## 4) Опционально: собрать ZIP для загрузки (на вашем ПК)

Если удобнее заливать архивом, выполните в PowerShell:

```powershell
$root = "C:\Users\User\рабочая"
$zip  = Join-Path $root "deploy-kvadratyra.zip"
if (Test-Path $zip) { Remove-Item $zip -Force }

$files = @(
  "wp-content\themes\kvadratyra-theme\header.php",
  "wp-content\themes\kvadratyra-theme\functions.php",
  "wp-content\themes\kvadratyra-theme\front-page.php",
  "wp-content\themes\kvadratyra-theme\single.php",
  "wp-content\themes\kvadratyra-theme\kv-geo.php",
  "wp-content\themes\kvadratyra-theme\footer.php",
  "wp-content\themes\kvadratyra-theme\style.css",
  "wp-content\themes\kvadratyra-theme\assets\dist\main.js",
  "wp-content\themes\kvadratyra-theme\assets\dist\style.css",
  "wp-content\mu-plugins\kv-geo\lib\data.php",
  "wp-content\mu-plugins\kv-geo\lib\routing.php",
  "wp-content\mu-plugins\kv-geo\lib\indexnow.php",
  "wp-content\mu-plugins\kv-geo\data\partners.json",
  "wp-content\mu-plugins\kv-geo\data\partner-points.json",
  "wp-content\mu-plugins\kv-geo\data\cities.json"
)

Compress-Archive -Path ($files | ForEach-Object { Join-Path $root $_ }) -DestinationPath $zip
$zip
```

Дальше распаковать на сервере в корень сайта, **с сохранением путей**.

## 5) Автодеплой по FTP (одна команда)

Если на хостинге есть FTP-доступ:

1. **Один раз настроить доступ**
   - Скопировать `deploy/env.example` в `deploy/.env` **или** добавить переменные FTP в `automation/wp_autopost/.env` (скрипт подхватит оба файла).
   - Вписать: `FTP_HOST` (для Beget обычно `ftp.kvadratyra.ru`), `FTP_USER`, `FTP_PASSWORD`; при необходимости `FTP_SECURE=true`, `REMOTE_ROOT=/public_html`.

2. **Установить зависимости и задеплоить**
   - Из корня проекта:
   ```bash
   cd deploy
   npm install
   npm run deploy
   ```
   Скрипт соберёт список файлов из п.1, подключится по FTP и зальёт их в корень сайта.

Перед первым деплоем сделайте сборку темы: из корня проекта `npm run build --prefix wp-content/themes/kvadratyra-theme`.

