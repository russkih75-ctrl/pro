# AGENTS.md

## Cursor Cloud specific instructions

### Обзор проекта

**WordPress-сайт** `kvadratyra.ru` — строительные/кровельные услуги, Воронежская область. Локального стека WordPress/MySQL/Apache нет; сайт размещён на хостинге Beget, деплой по FTP.

Компоненты, которые можно собирать и тестировать локально:

| Компонент | Директория | Основные команды |
|---|---|---|
| Фронтенд темы (Vite) | `wp-content/themes/kvadratyra-theme/` | `npm run dev`, `npm run build` |
| Тулкит деплоя + аудит | `deploy/` | `npm test` (8 юнит-тестов), `npm run audit:server` (нужны FTP-креды) |
| Бот автопостинга WP | `automation/wp_autopost/` | Python + Playwright (нужны WP-креды) |

### Запуск сервисов

- **Vite dev server**: `cd wp-content/themes/kvadratyra-theme && npm run dev` — запускается на `http://localhost:5173/`. Основная команда для разработки JS/CSS темы.
- **Сборка Vite**: `npm run build` в той же директории — выходные файлы `assets/dist/main.js` и `assets/dist/style.css`.
- **Тесты деплой-тулкита**: `cd deploy && npm test` — 8 тестов через встроенный test runner Node.js (`node --test audit/*.test.mjs`). Внешние сервисы не нужны.

### Линтинг

ESLint/Prettier/Stylelint/PHPCS в репозитории не настроены. Единственная автоматическая проверка качества — `npm test` в `deploy/`.

### Деплой

См. `DEPLOY.md` для ручного FTP-деплоя и `deploy/README.md` для автоматического аудит-тулкита. FTP-креды настраиваются в `deploy/.env`.

### Важные нюансы

- Vite-конфиг задаёт `root` как директорию темы, выходная папка — `assets/dist/`. Настройка `base: './'` критична для путей к ассетам в WordPress.
- Python-скрипты Playwright в `automation/wp_autopost/` требуют `playwright install chromium` после `pip install`.
- Команда `npm run audit:server` в деплой-тулките требует FTP-креды; для HTTP-режима без FTP используйте `AUDIT_SKIP_FTP=1`.
