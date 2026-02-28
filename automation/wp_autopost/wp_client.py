"""
WordPress browser automation client for kvadratyra.ru
Uses Playwright (async) and stores cookies via storage_state.
"""

import asyncio
import json
import os
import sys
import logging
import re
from datetime import datetime
from typing import Optional, Dict, Any

from dotenv import load_dotenv
from playwright.async_api import async_playwright, Page, Browser, BrowserContext


# Windows: keep UTF-8 for Cyrillic in logs
try:
    sys.stdout.reconfigure(encoding="utf-8")
    sys.stderr.reconfigure(encoding="utf-8")
except Exception:
    pass


logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler("autopost_debug.log", encoding="utf-8"),
    ],
)
logger = logging.getLogger("wp_autopost")

# Load .env next to this script first (important on Windows when cwd has Cyrillic issues)
_ENV_PATH = os.path.join(os.path.dirname(__file__), ".env")
load_dotenv(dotenv_path=_ENV_PATH, override=False)


class WordpressClient:
    def __init__(self):
        self.base_url = (os.getenv("WP_BASE_URL") or "").rstrip("/")
        self.user = os.getenv("WP_USER") or ""
        self.password = os.getenv("WP_PASS") or ""

        self.headless = (os.getenv("HEADLESS", "false").lower() == "true")
        self.timeout_ms = int(os.getenv("BROWSER_TIMEOUT", "60000"))
        self.storage_state_path = os.getenv("STORAGE_STATE", "wp_storage_state.json")
        self.keep_open = (os.getenv("KEEP_BROWSER_OPEN", "false").lower() == "true")

        self.playwright = None
        self.browser: Optional[Browser] = None
        self.context: Optional[BrowserContext] = None
        self.page: Optional[Page] = None

        if not self.base_url:
            guessed = self._guess_base_url_from_storage()
            if guessed:
                self.base_url = guessed

        if not self.base_url:
            raise ValueError("Set WP_BASE_URL in .env")

    def _guess_base_url_from_storage(self) -> str:
        path = self.storage_state_path
        if not path or not os.path.exists(path):
            return ""
        try:
            with open(path, "r", encoding="utf-8") as f:
                data = json.load(f)
            cookies = data.get("cookies") or []
            for c in cookies:
                domain = (c.get("domain") or "").lstrip(".")
                if domain and "." in domain:
                    return f"https://{domain}"
        except Exception:
            return ""
        return ""

    async def start(self):
        self.playwright = await async_playwright().start()
        self.browser = await self.playwright.chromium.launch(headless=self.headless)

        context_kwargs: Dict[str, Any] = {
            "viewport": {"width": 1440, "height": 900},
            "user_agent": (
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36"
            ),
        }
        if self.storage_state_path and os.path.exists(self.storage_state_path):
            context_kwargs["storage_state"] = self.storage_state_path
            logger.info("Loaded storage_state=%s", self.storage_state_path)

        self.context = await self.browser.new_context(**context_kwargs)
        self.page = await self.context.new_page()
        self.page.set_default_timeout(self.timeout_ms)

        self.page.on("console", lambda msg: logger.debug("[BROWSER] %s: %s", msg.type, msg.text))
        self.page.on("pageerror", lambda err: logger.error("[PAGEERROR] %s", err))

    async def close(self):
        if self.keep_open:
            logger.info("KEEP_BROWSER_OPEN=true — leaving browser open.")
            return
        if self.context:
            await self.context.close()
        if self.browser:
            await self.browser.close()
        if self.playwright:
            await self.playwright.stop()

    async def screenshot(self, name: str):
        if not self.page:
            return
        fn = f"{name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
        await self.page.screenshot(path=fn, full_page=True)
        logger.info("Screenshot: %s", fn)

    async def save_storage(self):
        if self.context and self.storage_state_path:
            await self.context.storage_state(path=self.storage_state_path)
            logger.info("Saved storage_state=%s", self.storage_state_path)

    async def login(self) -> bool:
        """
        Login to wp-admin via /wp-login.php.
        Reuses storage_state when available.
        """
        assert self.page is not None

        admin_url = f"{self.base_url}/wp-admin/"
        await self.page.goto(admin_url, wait_until="domcontentloaded")
        await self.page.wait_for_timeout(1000)

        # If already logged in, wp-admin loads dashboard (has #wpadminbar)
        if await self.page.locator("#wpadminbar").count() > 0:
            logger.info("Already logged in (wpadminbar detected).")
            return True

        # Otherwise, we should be on wp-login.php
        if "wp-login.php" not in self.page.url:
            await self.page.goto(f"{self.base_url}/wp-login.php", wait_until="domcontentloaded")

        if not self.user or not self.password:
            logger.error("No WP_USER/WP_PASS in .env and storage session is not valid.")
            return False

        await self.page.locator("input#user_login, input[name='log']").first.fill(self.user)
        await self.page.locator("input#user_pass, input[name='pwd']").first.fill(self.password)

        # Login button (RU/EN)
        btn = self.page.locator("#wp-submit").first
        if await btn.count() == 0:
            btn = self.page.get_by_role("button", name=re.compile(r"(Войти|Log in|Log In)", re.I)).first
        await btn.click()

        await self.page.wait_for_timeout(1500)
        await self.page.goto(admin_url, wait_until="domcontentloaded")

        ok = (await self.page.locator("#wpadminbar").count()) > 0
        if ok:
            logger.info("Login OK.")
            await self.save_storage()
            return True

        logger.error("Login failed. Current URL=%s", self.page.url)
        await self.screenshot("login_failed")
        return False

    async def _dismiss_editor_popups(self):
        """
        Gutenberg sometimes shows welcome guides/modals. We aggressively close common ones.
        Safe to call multiple times.
        """
        assert self.page is not None

        candidates = [
            "button[aria-label='Закрыть диалог']",
            "button[aria-label='Close dialog']",
            "button[aria-label^='Закрыть']",
            "button[aria-label^='Close']",
            "button:has-text('Понятно')",
            "button:has-text('Начать')",
            "button:has-text('Got it')",
            "button:has-text('Start')",
        ]

        for _ in range(3):
            closed_any = False
            for sel in candidates:
                loc = self.page.locator(sel).first
                if await loc.count() > 0:
                    try:
                        await loc.click(timeout=1500)
                        await self.page.wait_for_timeout(300)
                        closed_any = True
                    except Exception:
                        pass
            if not closed_any:
                break

    async def _fill_title(self, title: str):
        assert self.page is not None

        # Prefer classic textarea title
        locators = [
            "textarea.editor-post-title__input",
            "h1.wp-block-post-title[contenteditable='true']",
            "h1[contenteditable='true']",
            "[aria-label='Добавить заголовок']",
            "[aria-label='Add title']",
        ]
        for sel in locators:
            l = self.page.locator(sel).first
            if await l.count() == 0:
                continue
            try:
                await l.click(timeout=4000)
                # Some contenteditable elements don't support .fill reliably; use keyboard
                try:
                    await l.fill(title, timeout=4000)
                except Exception:
                    await self.page.keyboard.press("Control+A")
                    await self.page.keyboard.type(title, delay=5)
                return
            except Exception:
                continue
        raise RuntimeError("Could not locate post title field")

    async def _insert_custom_html_block(self):
        """
        Insert a 'Custom HTML' block using inserter + search (more stable than /html).
        """
        assert self.page is not None

        # Click into editor writing area first
        writing = self.page.locator(".block-editor-writing-flow, [aria-label*='редактора'], [aria-label*='Editor']").first
        if await writing.count() > 0:
            try:
                await writing.click(timeout=4000)
            except Exception:
                await self.page.mouse.click(200, 400)
        else:
            await self.page.mouse.click(200, 400)

        inserter = self.page.locator(
            "button.block-editor-inserter__toggle, button[aria-label='Добавить блок'], button[aria-label='Add block']"
        ).first

        if await inserter.count() > 0:
            await inserter.click()

            search = self.page.locator(
                "input[aria-label='Поиск блоков'], input[aria-label='Search for blocks'], input[placeholder*='Поиск'], input[placeholder*='Search']"
            ).first
            if await search.count() > 0:
                await search.fill("HTML")

            # Choose block
            btn = self.page.locator("button:has-text('Произвольный HTML'), button:has-text('Custom HTML')").first
            if await btn.count() > 0:
                await btn.click()
                return

        # Fallback: slash command
        await self.page.keyboard.type("/html", delay=10)
        await self.page.keyboard.press("Enter")

    async def _fill_custom_html(self, html: str):
        assert self.page is not None
        candidates = [
            ".wp-block-html textarea",
            ".block-editor-block-list__block[data-type='core/html'] textarea",
            "textarea.block-editor-plain-text",
            "textarea",
        ]
        for sel in candidates:
            t = self.page.locator(sel).first
            if await t.count() == 0:
                continue
            try:
                await t.wait_for(state="visible", timeout=8000)
                await t.fill(html)
                return
            except Exception:
                continue
        raise RuntimeError("Could not locate Custom HTML textarea")

    async def _download_to_temp(self, url: str) -> Optional[str]:
        try:
            import requests
        except Exception:
            return None

        if not url:
            return None

        try:
            r = requests.get(url, timeout=25, headers={"User-Agent": "Mozilla/5.0"})
            if r.status_code < 200 or r.status_code >= 300:
                return None
            ext = ".jpg"
            m = (r.headers.get("content-type") or "").lower()
            if "png" in m:
                ext = ".png"
            elif "webp" in m:
                ext = ".webp"
            elif "jpeg" in m or "jpg" in m:
                ext = ".jpg"
            fn = f"featured_{datetime.now().strftime('%Y%m%d_%H%M%S')}{ext}"
            with open(fn, "wb") as f:
                f.write(r.content)
            return fn
        except Exception:
            return None

    async def _set_featured_image_from_url(self, url: str) -> bool:
        """
        Downloads image and sets it as featured image via Media modal.
        """
        assert self.page is not None
        if not url:
            return False

        path = await self._download_to_temp(url)
        if not path:
            logger.error("Failed to download featured image: %s", url)
            return False

        try:
            # Ensure sidebar is visible (settings button in top-right)
            sidebar = self.page.locator(".edit-post-sidebar").first
            if await sidebar.count() == 0:
                settings_btn = self.page.locator(
                    "button[aria-label='Настройки'], button[aria-label='Settings'], button[aria-label^='Настройки']"
                ).first
                if await settings_btn.count() > 0:
                    await settings_btn.click()

            # Open Featured image panel/button
            open_btn = self.page.locator(
                "button:has-text('Изображение записи'), button:has-text('Featured image')"
            ).first
            if await open_btn.count() > 0:
                await open_btn.click()

            set_btn = self.page.locator(
                "button:has-text('Установить изображение записи'), button:has-text('Set featured image')"
            ).first
            if await set_btn.count() == 0:
                # Sometimes it's a link-like button
                set_btn = self.page.locator(
                    "a:has-text('Установить изображение записи'), a:has-text('Set featured image')"
                ).first

            if await set_btn.count() == 0:
                logger.error("Featured image button not found")
                return False

            await set_btn.click()

            # Switch to Upload files tab if present
            upload_tab = self.page.locator("button:has-text('Загрузить файлы'), button:has-text('Upload files')").first
            if await upload_tab.count() > 0:
                await upload_tab.click()

            # Trigger file chooser
            file_input = self.page.locator("input[type='file']").first
            if await file_input.count() > 0:
                await file_input.set_input_files(path)
            else:
                choose_btn = self.page.locator("button:has-text('Выбрать файлы'), button:has-text('Select Files')").first
                if await choose_btn.count() > 0:
                    async with self.page.expect_file_chooser() as fc:
                        await choose_btn.click()
                    chooser = await fc.value
                    await chooser.set_files(path)
                else:
                    logger.error("Could not find file input/chooser in Media modal")
                    return False

            # Wait a bit for upload and selection
            await self.page.wait_for_timeout(2500)

            confirm = self.page.locator(
                "button:has-text('Установить изображение записи'), button:has-text('Set featured image')"
            ).last
            if await confirm.count() > 0:
                await confirm.click()
                await self.page.wait_for_timeout(1200)
                logger.info("Featured image set.")
                return True

            logger.error("Featured image confirm button not found")
            return False
        finally:
            try:
                os.remove(path)
            except Exception:
                pass

    async def _save_or_publish(self, status: str = "draft") -> None:
        assert self.page is not None
        status = (status or "draft").lower().strip()

        if status == "publish":
            pub = self.page.locator(
                "button.editor-post-publish-button, button:has-text('Опубликовать'), button:has-text('Publish')"
            ).first
            if await pub.count() > 0:
                await pub.click()
                await self.page.wait_for_timeout(800)

                confirm = self.page.locator(
                    "button.editor-post-publish-button__button, button:has-text('Опубликовать')"
                ).first
                if await confirm.count() > 0:
                    await confirm.click()
            return

        # Draft / save
        save = self.page.locator(
            "button.editor-post-save-draft, button:has-text('Сохранить черновик'), button:has-text('Save draft')"
        ).first
        if await save.count() > 0:
            await save.click()

    async def create_post_draft_from_html(self, title: str, html: str, post_type: str = "post") -> bool:
        """
        Create a post (or custom post type) in Gutenberg using a Custom HTML block.
        """
        assert self.page is not None
        post_type = (post_type or "post").strip().lower()
        if not re.fullmatch(r"[a-z0-9_-]+", post_type):
            post_type = "post"

        editor_url = f"{self.base_url}/wp-admin/post-new.php"
        if post_type != "post":
            editor_url += f"?post_type={post_type}"

        await self.page.goto(editor_url, wait_until="domcontentloaded")
        await self.page.wait_for_timeout(1500)

        await self._dismiss_editor_popups()
        await self._fill_title(title)

        await self._insert_custom_html_block()
        await self._fill_custom_html(html)

        await self._save_or_publish("draft")
        await self.page.wait_for_timeout(2000)
        logger.info("Draft saved (%s): %s", post_type, title)
        return True


async def run_from_file(path: str):
    with open(path, "r", encoding="utf-8") as f:
        data = json.load(f)
    title = data.get("title") or "Автопост"
    html = data.get("html") or data.get("content") or "<p>—</p>"
    featured_url = data.get("featured_image_url") or data.get("cover_url") or ""
    status = (data.get("status") or "draft").lower().strip()
    post_type = (data.get("post_type") or "post").strip().lower()

    client = WordpressClient()
    try:
        await client.start()
        if not await client.login():
            raise RuntimeError("Login failed")
        await client.create_post_draft_from_html(title, html, post_type=post_type)
        if featured_url:
            await client._set_featured_image_from_url(featured_url)
        await client._save_or_publish(status)
        await client.screenshot(f"{post_type}_draft_created")
    finally:
        await client.close()


if __name__ == "__main__":
    import argparse

    p = argparse.ArgumentParser(description="WordPress autopost via Playwright (Gutenberg)")
    p.add_argument("file", nargs="?", default=None, help="Path to article JSON (positional)")
    p.add_argument("--file", dest="file_opt", default=None, help="Path to article JSON")
    args = p.parse_args()

    file_path = args.file_opt or args.file or os.path.join("articles", "example.json")
    asyncio.run(run_from_file(file_path))

