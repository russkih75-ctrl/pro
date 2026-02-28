import { Client } from "basic-ftp";
import dotenv from "dotenv";
import { createHash } from "crypto";
import { existsSync, statSync, readFileSync } from "fs";
import { mkdir, writeFile } from "fs/promises";
import { dirname, join } from "path";
import { fileURLToPath } from "url";
import { DEFAULT_DOCROOT_CANDIDATES, pickBestDocroot } from "./lib/docroot.mjs";
import { buildDriftRows, summarizeDrift } from "./lib/drift.mjs";
import { extractSeoSignals } from "./lib/httpAudit.mjs";

const __dirname = dirname(fileURLToPath(import.meta.url));
const DEPLOY_DIR = join(__dirname, "..");
const ROOT = join(DEPLOY_DIR, "..");
const REPORT_DIR = join(DEPLOY_DIR, "reports");

dotenv.config({ path: join(DEPLOY_DIR, ".env") });
dotenv.config({ path: join(ROOT, "automation", "wp_autopost", ".env"), override: false });

const SITE_URL = (process.env.SITE_URL || "https://kvadratyra.ru").replace(/\/$/, "");
const FILES = [
  "robots.txt",
  "wp-content/themes/kvadratyra-theme/functions.php",
  "wp-content/themes/kvadratyra-theme/inc/schema-graph.php",
  "wp-content/themes/kvadratyra-theme/inc/schema-article.php",
  "wp-content/themes/kvadratyra-theme/inc/trust-pages.php",
  "wp-content/themes/kvadratyra-theme/inc/ai-feed.php",
  "wp-content/themes/kvadratyra-theme/inc/llms-txt.php",
  "wp-content/themes/kvadratyra-theme/kv-geo.php",
  "wp-content/mu-plugins/kv-geo/lib/routing.php",
  "wp-content/mu-plugins/kv-geo/lib/sitemaps.php",
  "wp-content/mu-plugins/kv-geo/lib/indexnow.php",
];

const URLS = [
  `${SITE_URL}/robots.txt`,
  `${SITE_URL}/wp-sitemap.xml`,
  `${SITE_URL}/?kv_geo_sitemap=index`,
  `${SITE_URL}/kv-geo-sitemap-cities.xml`,
  `${SITE_URL}/kv-geo-sitemap-services.xml`,
  `${SITE_URL}/feed/dzen/`,
  `${SITE_URL}/ai-feed.json`,
  `${SITE_URL}/llms.txt`,
  `${SITE_URL}/llms-full.txt`,
  `${SITE_URL}/geo/`,
  `${SITE_URL}/o-nas/`,
  `${SITE_URL}/vakansii/`,
  `${SITE_URL}/kontakty/`,
  `${SITE_URL}/dostavka-oplata/`,
  `${SITE_URL}/garantiya/`,
  `${SITE_URL}/rekvizity/`,
];

function sha256(path) {
  const hash = createHash("sha256");
  hash.update(readFileSync(path));
  return hash.digest("hex");
}

function localMeta(relPath) {
  const p = join(ROOT, relPath);
  if (!existsSync(p)) return null;
  const stat = statSync(p);
  return {
    size: stat.size,
    mtime: stat.mtime.toISOString(),
    sha256: sha256(p),
  };
}

function impactFor(file) {
  if (file.endsWith("robots.txt")) return "high";
  if (file.includes("sitemap") || file.includes("routing.php")) return "high";
  if (file.includes("schema")) return "high";
  return "normal";
}

async function listNames(client, dir) {
  try {
    const list = await client.list(dir);
    return list.map((i) => i.name);
  } catch {
    return [];
  }
}

async function detectDocroot(client) {
  const candidates = {};
  for (const root of DEFAULT_DOCROOT_CANDIDATES) {
    candidates[root] = await listNames(client, root);
  }
  return { candidates, best: pickBestDocroot(candidates) };
}

async function serverMeta(client, docroot, relPath) {
  const remote = `${docroot}/${relPath}`.replace(/\\/g, "/").replace(/\/+/g, "/");
  const remoteDir = remote.replace(/\/[^/]+$/, "");
  const remoteFile = remote.split("/").pop();

  try {
    const list = await client.list(remoteDir);
    const item = list.find((f) => f.name === remoteFile);
    if (!item) return null;
    return {
      size: Number(item.size || 0),
      mtime: item.modifiedAt ? new Date(item.modifiedAt).toISOString() : "",
      sha256: null,
    };
  } catch {
    return null;
  }
}

async function fetchOne(url) {
  try {
    const res = await fetch(url, { redirect: "follow" });
    const body = await res.text();
    return {
      url,
      ok: res.ok,
      status: res.status,
      contentType: res.headers.get("content-type") || "",
      seo: body.includes("<html") ? extractSeoSignals(body, url) : null,
    };
  } catch (error) {
    return {
      url,
      ok: false,
      status: 0,
      error: String(error?.message || error),
      seo: null,
    };
  }
}

function driftToMarkdown(summary, rows) {
  const lines = [];
  lines.push("# Deployment Drift Report");
  lines.push("");
  lines.push(`- Total: ${summary.total}`);
  lines.push(`- Match: ${summary.match}`);
  lines.push(`- Mismatch: ${summary.mismatch}`);
  lines.push(`- Missing on server: ${summary.missing_on_server}`);
  lines.push(`- Server only: ${summary.server_only}`);
  lines.push("");
  lines.push("| File | Status | Impact |");
  lines.push("|---|---|---|");
  for (const row of rows) {
    lines.push(`| \`${row.file}\` | ${row.status} | ${row.impact} |`);
  }
  lines.push("");
  return lines.join("\n");
}

function httpToMarkdown(results) {
  const lines = [];
  lines.push("# HTTP Audit Report");
  lines.push("");
  lines.push("| URL | Status | Indexable | Canonical OK | Content-Type | Title | Canonical | OG URL | Twitter Card | JSON-LD |");
  lines.push("|---|---:|---:|---:|---|---|---|---|---|---:|");
  for (const item of results) {
    const seo = item.seo || {};
    lines.push(
      `| ${item.url} | ${item.status} | ${seo.indexable === false ? "0" : "1"} | ${seo.canonicalOk ? "1" : "0"} | ${item.contentType || ""} | ${seo.title || ""} | ${seo.canonical || ""} | ${seo.ogUrl || ""} | ${seo.twitterCard || ""} | ${seo.jsonLdCount || 0} |`
    );
  }
  lines.push("");
  return lines.join("\n");
}

async function main() {
  const host = process.env.FTP_HOST || process.env.FTP_HOSTNAME;
  const user = process.env.FTP_USER;
  const password = process.env.FTP_PASSWORD || process.env.FTP_PASS;
  const secure = /^(1|true|yes)$/i.test(process.env.FTP_SECURE || "");
  const skipFtp = /^(1|true|yes)$/i.test(process.env.AUDIT_SKIP_FTP || "");
  const forcedRemoteRoot = (process.env.REMOTE_ROOT || "").trim();

  await mkdir(REPORT_DIR, { recursive: true });

  const localMap = {};
  for (const file of FILES) localMap[file] = localMeta(file);

  let docroot = null;
  let driftRows = [];
  let driftSummary = null;

  if (!skipFtp && host && user && password) {
    const client = new Client(60000);
    try {
      await client.access({ host, user, password, secure });
      const detected = await detectDocroot(client);
      docroot = forcedRemoteRoot || detected.best?.root || null;

      const serverMap = {};
      for (const file of FILES) {
        serverMap[file] = docroot ? await serverMeta(client, docroot, file) : null;
      }
      driftRows = buildDriftRows(localMap, serverMap, impactFor);
      driftSummary = summarizeDrift(driftRows);

      const detectText = [
        "# Docroot Detection",
        "",
        `- Best candidate: ${detected.best?.root || "not found"}`,
        `- Using docroot: ${docroot || "not found"}`,
        "",
        "## Candidate scores",
      ];
      for (const [root, names] of Object.entries(detected.candidates)) {
        detectText.push(`- ${root}: ${names.join(", ")}`);
      }
      await writeFile(join(REPORT_DIR, "docroot-detection.md"), detectText.join("\n"), "utf-8");
    } finally {
      client.close();
    }
  } else {
    driftRows = buildDriftRows(localMap, {}, impactFor);
    driftSummary = summarizeDrift(driftRows);
    const reason = skipFtp ? "AUDIT_SKIP_FTP is enabled." : "FTP env vars are missing.";
    await writeFile(
      join(REPORT_DIR, "docroot-detection.md"),
      `# Docroot Detection\n\n${reason} Detection skipped.\n`,
      "utf-8"
    );
  }

  const httpResults = [];
  for (const url of URLS) {
    httpResults.push(await fetchOne(url));
  }

  await writeFile(join(REPORT_DIR, "deployment-drift.md"), driftToMarkdown(driftSummary, driftRows), "utf-8");
  await writeFile(join(REPORT_DIR, "http-audit.md"), httpToMarkdown(httpResults), "utf-8");

  console.log("Audit complete.");
  console.log(`Reports: ${REPORT_DIR}`);
  if (docroot) console.log(`Detected REMOTE_ROOT: ${docroot}`);
}

main().catch((error) => {
  console.error("Audit failed:", error);
  process.exit(1);
});
