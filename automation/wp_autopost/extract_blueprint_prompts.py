"""
Extracts actionable MCP prompt blocks from a Make blueprint JSON.

Usage:
  python extract_blueprint_prompts.py --blueprint "<path-to-blueprint.json>"
"""

from __future__ import annotations

import argparse
import json
from pathlib import Path
from typing import Any


MAX_LINK = "https://max.ru/join/Eb2wTRuozpHOjt2n5uXqyzN0ouN6KLubNFZc_Zg71lU"


def _walk(node: Any):
    if isinstance(node, dict):
        yield node
        for value in node.values():
            yield from _walk(value)
    elif isinstance(node, list):
        for item in node:
            yield from _walk(item)


def _is_text(value: Any) -> bool:
    return isinstance(value, str) and value.strip() != ""


def _build_guide_prompt_template() -> str:
    return (
        "Ты — практичный редактор kvadratyra. Пиши по-русски, без воды.\n\n"
        "Задача: сделать короткий полезный гайд «как сделать самому» по теме: {{TOPIC}}.\n"
        "Фокус: кровля/фасады/заборы (материалы + монтаж), без чужих брендов.\n\n"
        "Структура (HTML):\n"
        "1) <p>Direct Answer (до 50 слов): «[что это] — это [суть], которая [польза]».\n"
        "2) <h2>Что подготовить</h2> + <ul>чек-лист инструмента/материалов.\n"
        "3) <h2>Пошагово</h2> + 5-8 шагов (<h3>Шаг N</h3><p>...</p>).\n"
        "4) <h2>Типичные ошибки</h2> + <ul>.\n"
        "5) <h2>Проверка результата</h2> + короткий чек-лист.\n"
        "6) <h2>Если не получилось</h2> + мягкий CTA без давления.\n\n"
        "В самом конце отдельным абзацем добавь ссылки:\n"
        "- Avito: https://www.avito.ru/brands/kvadratyra\n"
        "- Telegram: https://t.me/kvadrat_yra\n"
        "- Телефон: 89003045190\n"
        f"- MAX чат помощи: {MAX_LINK}\n\n"
        "Выход: только готовый HTML."
    )


def extract_payload(blueprint: dict[str, Any]) -> dict[str, Any]:
    payload: dict[str, Any] = {
        "wordstat": [],
        "nanobanana": [],
        "wordpress": [],
        "article_prompts": [],
        "meta_prompts": [],
        "guide_prompt_template": _build_guide_prompt_template(),
        "guide_footer_links": {
            "avito": "https://www.avito.ru/brands/kvadratyra",
            "telegram": "https://t.me/kvadrat_yra",
            "phone": "89003045190",
            "max_chat": MAX_LINK,
        },
    }

    for block in _walk(blueprint):
        module = block.get("module")
        mapper = block.get("mapper") if isinstance(block.get("mapper"), dict) else {}

        if isinstance(module, str) and "wordstat" in module:
            payload["wordstat"].append(
                {
                    "module": module,
                    "user_text": mapper.get("user_text", ""),
                    "developer_text": mapper.get("developer_text", ""),
                }
            )

        if isinstance(module, str) and "nanobanana" in module:
            payload["nanobanana"].append(
                {
                    "module": module,
                    "prompt": mapper.get("prompt", ""),
                    "image_input": mapper.get("image_input", []),
                    "aspect_ratio": mapper.get("aspect_ratio", ""),
                    "resolution": mapper.get("resolution", ""),
                    "output_format": mapper.get("output_format", ""),
                }
            )

        if isinstance(module, str) and "wpfree" in module:
            payload["wordpress"].append(
                {
                    "module": module,
                    "mapper_keys": sorted(mapper.keys()),
                }
            )

        user_text = mapper.get("user_text")
        developer_text = mapper.get("developer_text")
        if _is_text(user_text) and "системный редактор, валидатор и трансформатор контента" in user_text.lower():
            payload["article_prompts"].append(
                {
                    "user_text": user_text,
                    "developer_text": developer_text or "",
                }
            )
        if _is_text(developer_text) and "мета-описание" in developer_text.lower():
            payload["meta_prompts"].append(
                {
                    "user_text": user_text or "",
                    "developer_text": developer_text,
                }
            )

    return payload


def write_markdown_runbook(target: Path, payload: dict[str, Any]) -> None:
    wordstat_count = len(payload["wordstat"])
    nano_count = len(payload["nanobanana"])
    wp_count = len(payload["wordpress"])

    lines = [
        "# Blueprint -> MCP -> WordPress Runbook",
        "",
        "## 1) Wordstat (MCP)",
        f"- Найдено блоков: {wordstat_count}",
        "- Возьми первый `user_text` + `developer_text` из `blueprint-prompts.json` и запусти запросы.",
        "- Результат: 3-10 ключевых фраз для статьи и гайда.",
        "",
        "## 2) Nano Banana (MCP)",
        f"- Найдено блоков: {nano_count}",
        "- Возьми `prompt` и `image_input` из блока nanobanana.",
        "- Сгенерируй 1 обложку для статьи и 1 обложку для гайда.",
        "",
        "## 3) Контент",
        "- Статья: используй `article_prompts` + ключи Wordstat + фактуру.",
        "- Гайд: используй `guide_prompt_template` (direct answer + пошагово + чек-лист).",
        "",
        "## 4) Публикация в WordPress",
        f"- В blueprint обнаружено WordPress-блоков: {wp_count}",
        "- Публикуй через MCP WordPress инструмент сразу в `publish`:",
        "  - `post_type=post` для статьи",
        "  - `post_type=guide` для гайда",
        "",
        "## 5) Хвост ссылок в гайде",
        "- Avito: https://www.avito.ru/brands/kvadratyra",
        "- Telegram: https://t.me/kvadrat_yra",
        "- Телефон: 89003045190",
        f"- MAX чат помощи: {MAX_LINK}",
        "",
        "## 6) Проверка Дзен RSS",
        "- Проверь, что обе публикации появились в `/feed/dzen/`.",
    ]

    target.write_text("\n".join(lines) + "\n", encoding="utf-8")


def main() -> int:
    parser = argparse.ArgumentParser(description="Extract MCP-ready prompts from blueprint JSON")
    parser.add_argument("--blueprint", required=True, help="Absolute path to blueprint JSON file")
    parser.add_argument(
        "--out-dir",
        default=str(Path(__file__).resolve().parent / "out"),
        help="Output directory for extracted files",
    )
    args = parser.parse_args()

    blueprint_path = Path(args.blueprint)
    out_dir = Path(args.out_dir)
    out_dir.mkdir(parents=True, exist_ok=True)

    with blueprint_path.open("r", encoding="utf-8") as f:
        blueprint = json.load(f)

    payload = extract_payload(blueprint)
    json_out = out_dir / "blueprint-prompts.json"
    md_out = out_dir / "blueprint-runbook.md"

    json_out.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    write_markdown_runbook(md_out, payload)

    print(f"Saved: {json_out}")
    print(f"Saved: {md_out}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
