# Bonsai → Claude Code (Cursor)

Этот набор файлов автоматизирует настройку из гайда “Bonsai + Claude Code в Cursor”.

## Что делает

Скрипт создаёт/обновляет файл:

- `%USERPROFILE%\.claude\settings.json`

и записывает туда:

- `ANTHROPIC_BASE_URL = https://go.trybons.ai`
- `ANTHROPIC_AUTH_TOKEN = <ваш токен Bonsai>`
- `ANTHROPIC_API_KEY = <ваш токен Bonsai>`

При наличии существующего `settings.json` — **аккуратно мерджит** структуру и не трогает другие поля, кроме `env.*`.

## Как получить токен Bonsai

- Откройте `https://app.trybons.ai/api-keys`
- `Create API Key`
- Скопируйте ключ (обычно начинается с `sk_cr_...`)

## Запуск (PowerShell)

Из корня проекта:

```powershell
.\automation\claude_bonsai\setup-bonsai-claude.ps1 -Token "sk_cr_..." -Backup
```

Если не передавать `-Token`, скрипт попросит вставить ключ в интерактивном вводе:

```powershell
.\automation\claude_bonsai\setup-bonsai-claude.ps1 -Backup
```

Проверочный прогон без записи:

```powershell
.\automation\claude_bonsai\setup-bonsai-claude.ps1 -Token "sk_cr_..." -DryRun
```

## Дальше в Cursor

- Перезапустите Cursor.
- Откройте Claude Code.
- Если всплывает логин (“How do you want to log in?”) — закройте или нажмите **Maybe later** (логин не нужен, запросы идут через Bonsai).

## Шаблон конфига

Если нужно вручную, смотрите `settings.template.json`.
