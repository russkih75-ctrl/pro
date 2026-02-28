### WP Autopost (через браузер / Playwright)

Файлы:
- `wp_client.py`: логин в WordPress и создание черновика через Gutenberg (Custom HTML block)
- `articles/example.json`: пример статьи
- `config.example.env`: пример переменных окружения
- `extract_blueprint_prompts.py`: извлечение пошаговых промптов из blueprint в MCP-ready формат

### Установка (Windows)

Команды (в PowerShell), запускать из папки `automation/wp_autopost`:

```bash
python -m venv .venv
.venv\\Scripts\\activate
python -m pip install -r requirements.txt
python -m playwright install chromium
```

Скопируй `config.example.env` в `.env` и заполни `WP_PASS`.

### Важно про Windows + кириллицу в путях

Если папка проекта лежит в пути с кириллицей (например `C:\\Users\\User\\рабочая\\...`), PowerShell иногда ломает `cd` и Playwright.

Решение: запускать из ASCII short-path (8.3).

Как найти short-path (пример):

```bash
cmd /c dir /x "C:\Users\User"
```

Ищем строку с папкой `рабочая` и её short name (например `9F03~1`). Далее используем:

```bash
cd C:\Users\User\9F03~1\automation\wp_autopost
.\.venv\Scripts\python wp_client.py articles\example.json
```

### Тест

```bash
python wp_client.py articles/example.json
```

Альтернатива (если удобнее флагом):

```bash
python wp_client.py --file articles/example.json
```

Формат JSON (минимум):

```json
{
  "title": "Заголовок",
  "html": "<p>HTML контент</p>",
  "post_type": "post",
  "status": "draft",
  "featured_image_url": "https://.../cover.jpg"
}
```

`post_type` поддерживает:
- `post` — обычная статья
- `guide` — гайд (CPT из темы)

## Извлечение шагов/промптов из blueprint (MCP pipeline)

Команда:

```bash
python extract_blueprint_prompts.py --blueprint "C:\Users\User\Downloads\RU KVADRATYRA- КРОВЛЯ-МАТЕРИАЛЫ — КОНТЕНТ 2026.blueprint (13).json"
```

Результат:
- `out/blueprint-prompts.json` — извлеченные блоки для Wordstat/NanoBanana/WordPress + шаблон гайда
- `out/blueprint-runbook.md` — пошаговый чеклист до публикации и проверки RSS `/feed/dzen/`
