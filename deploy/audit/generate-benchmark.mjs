import { existsSync, readFileSync } from "fs";
import { mkdir, writeFile } from "fs/promises";
import { dirname, join } from "path";
import { fileURLToPath } from "url";

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, "..", "..");
const REPORT_DIR = join(ROOT, "deploy", "reports");
const SOURCE = join(ROOT, "mayai_audit.md");
const TARGET = join(REPORT_DIR, "benchmark-mayai-gap.md");

const header = `# Benchmark vs mayai.ru

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

`;

async function main() {
  await mkdir(REPORT_DIR, { recursive: true });

  let notes = "No local `mayai_audit.md` file found.\n";
  if (existsSync(SOURCE)) {
    notes = readFileSync(SOURCE, "utf-8");
  }

  await writeFile(TARGET, header + notes, "utf-8");
  console.log(`Written: ${TARGET}`);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
