# Blueprint -> MCP -> WordPress Runbook

## 1) Wordstat (MCP)
- Найдено блоков: 1
- Возьми первый `user_text` + `developer_text` из `blueprint-prompts.json` и запусти запросы.
- Результат: 3-10 ключевых фраз для статьи и гайда.

## 2) Nano Banana (MCP)
- Найдено блоков: 2
- Возьми `prompt` и `image_input` из блока nanobanana.
- Сгенерируй 1 обложку для статьи и 1 обложку для гайда.

## 3) Контент
- Статья: используй `article_prompts` + ключи Wordstat + фактуру.
- Гайд: используй `guide_prompt_template` (direct answer + пошагово + чек-лист).

## 4) Публикация в WordPress
- В blueprint обнаружено WordPress-блоков: 3
- Публикуй через MCP WordPress инструмент сразу в `publish`:
  - `post_type=post` для статьи
  - `post_type=guide` для гайда

## 5) Хвост ссылок в гайде
- Avito: https://www.avito.ru/brands/kvadratyra
- Telegram: https://t.me/kvadrat_yra
- Телефон: 89003045190
- MAX чат помощи: https://max.ru/join/Eb2wTRuozpHOjt2n5uXqyzN0ouN6KLubNFZc_Zg71lU

## 6) Проверка Дзен RSS
- Проверь, что обе публикации появились в `/feed/dzen/`.
