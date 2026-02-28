import { Client } from "basic-ftp";
import { existsSync } from "fs";
import { join, dirname } from "path";
import { fileURLToPath } from "url";
import dotenv from "dotenv";

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, "..");
// Сначала deploy/.env, потом automation/wp_autopost/.env (если есть FTP_* там)
dotenv.config({ path: join(__dirname, ".env") });
dotenv.config({ path: join(ROOT, "automation", "wp_autopost", ".env"), override: false });

// То же, что в DEPLOY.md
const FILES = [
  "robots.txt",
  "kv-geo-sitemap.xml",
  "feed/dzen/index.php",
  "wp-content/themes/kvadratyra-theme/header.php",
  "wp-content/themes/kvadratyra-theme/functions.php",
  "wp-content/themes/kvadratyra-theme/front-page.php",
  "wp-content/themes/kvadratyra-theme/single.php",
  "wp-content/themes/kvadratyra-theme/template-parts/quiz-calculator.php",
  "wp-content/themes/kvadratyra-theme/kv-geo.php",
  "wp-content/themes/kvadratyra-theme/footer.php",
  "wp-content/themes/kvadratyra-theme/style.css",
  "wp-content/themes/kvadratyra-theme/inc/schema-graph.php",
  "wp-content/themes/kvadratyra-theme/inc/schema-article.php",
  "wp-content/themes/kvadratyra-theme/inc/trust-pages.php",
  "wp-content/themes/kvadratyra-theme/inc/calc-ajax.php",
  "wp-content/themes/kvadratyra-theme/inc/ai-feed.php",
  "wp-content/themes/kvadratyra-theme/inc/llms-txt.php",
  "wp-content/themes/kvadratyra-theme/assets/dist/main.js",
  "wp-content/themes/kvadratyra-theme/assets/dist/style.css",
  "wp-content/themes/kvadratyra-theme/assets/favicons/favicon.svg",
  "wp-content/themes/kvadratyra-theme/assets/favicons/icon-192.svg",
  "wp-content/themes/kvadratyra-theme/assets/favicons/icon-512.svg",
  "wp-content/themes/kvadratyra-theme/assets/favicons/site.webmanifest",
  "wp-content/mu-plugins/kv-geo/lib/data.php",
  "wp-content/mu-plugins/kv-geo/lib/routing.php",
  "wp-content/mu-plugins/kv-geo/lib/indexnow.php",
  "wp-content/mu-plugins/kv-geo/lib/sitemaps.php",
  "wp-content/mu-plugins/kv-geo/data/partners.json",
  "wp-content/mu-plugins/kv-geo/data/partner-points.json",
  "wp-content/mu-plugins/kv-geo/data/cities.json",
];

async function main() {
  const host = process.env.FTP_HOST || process.env.FTP_HOSTNAME;
  const user = process.env.FTP_USER;
  const password = process.env.FTP_PASSWORD || process.env.FTP_PASS;
  const secure = /^(1|true|yes)$/i.test(process.env.FTP_SECURE || "");
  const remoteRoot = (process.env.REMOTE_ROOT || "/").replace(/\/$/, "") || "/";

  if (!host || !user || !password) {
    console.error("Задайте FTP_HOST, FTP_USER, FTP_PASSWORD в deploy/.env (шаблон: deploy/env.example).");
    process.exit(1);
  }

  const client = new Client(60_000);
  client.ftp.verbose = false;

  try {
    await client.access({
      host,
      user,
      password,
      secure,
    });
    console.log("Подключение к FTP: OK");

    for (const file of FILES) {
      const localPath = join(ROOT, file);
      if (!existsSync(localPath)) {
        console.warn("Пропуск (нет файла):", file);
        continue;
      }
      const remotePath = (remoteRoot + "/" + file).replace(/\\/g, "/").replace(/\/+/g, "/");
      const remoteDir = remotePath.replace(/\/[^/]+$/, "");
      await client.ensureDir(remoteDir);
      await client.uploadFrom(localPath, remotePath);
      console.log("↑", file);
    }

    console.log("Деплой завершён. Очистите кэш и проверьте сайт.");
  } catch (err) {
    console.error("Ошибка FTP:", err.message);
    process.exit(1);
  } finally {
    client.close();
  }
}

main();
