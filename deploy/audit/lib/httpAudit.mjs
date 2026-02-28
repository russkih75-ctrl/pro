const META_REGEX = /<meta\s+[^>]*>/gi;

function attr(node, name) {
  const re = new RegExp(`${name}\\s*=\\s*["']([^"']*)["']`, "i");
  const m = node.match(re);
  return m ? m[1] : "";
}

export function parseJsonLdBlocks(html) {
  if (!html) return [];
  const out = [];
  const re = /<script[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi;
  let m;
  while ((m = re.exec(html)) !== null) {
    const raw = (m[1] || "").trim();
    if (!raw) continue;
    try {
      out.push(JSON.parse(raw));
    } catch {
      out.push({ _invalidJsonLd: true, raw });
    }
  }
  return out;
}

function normalizeUrl(value) {
  if (!value) return "";
  return String(value).trim().replace(/\/+$/, "");
}

function isCanonicalConsistent(requestUrl, canonicalUrl) {
  if (!canonicalUrl) return false;
  const a = normalizeUrl(requestUrl);
  const b = normalizeUrl(canonicalUrl);
  if (!a || !b) return false;
  return a === b;
}

export function extractSeoSignals(html, url) {
  const titleMatch = html?.match(/<title>([\s\S]*?)<\/title>/i);
  const title = titleMatch ? titleMatch[1].trim() : "";

  let description = "";
  let robots = "";
  let canonical = "";
  let ogTitle = "";
  let ogDescription = "";
  let ogUrl = "";
  let twitterCard = "";

  const metas = html?.match(META_REGEX) || [];
  for (const node of metas) {
    const name = attr(node, "name").toLowerCase();
    const property = attr(node, "property").toLowerCase();
    const content = attr(node, "content");
    if (name === "description" && !description) description = content;
    if (name === "robots" && !robots) robots = content;
    if (property === "og:title" && !ogTitle) ogTitle = content;
    if (property === "og:description" && !ogDescription) ogDescription = content;
    if (property === "og:url" && !ogUrl) ogUrl = content;
    if (name === "twitter:card" && !twitterCard) twitterCard = content;
  }

  const canonicalMatch = html?.match(/<link\s+[^>]*rel=["']canonical["'][^>]*>/i);
  if (canonicalMatch) canonical = attr(canonicalMatch[0], "href");
  const canonicalOk = isCanonicalConsistent(url, canonical);
  const indexable = robots ? !/\bnoindex\b/i.test(robots) : true;

  return {
    url,
    title,
    description,
    robots,
    indexable,
    canonical,
    canonicalOk,
    ogTitle,
    ogDescription,
    ogUrl,
    twitterCard,
    jsonLdCount: parseJsonLdBlocks(html).length,
  };
}
