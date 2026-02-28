import test from "node:test";
import assert from "node:assert/strict";
import { extractSeoSignals, parseJsonLdBlocks } from "./lib/httpAudit.mjs";

test("extractSeoSignals gets key SEO tags", () => {
  const html = `
    <html>
      <head>
        <title>Test Page</title>
        <meta name="description" content="Desc text">
        <meta name="robots" content="index,follow">
        <meta property="og:title" content="OG Test Page">
        <meta property="og:description" content="OG Desc">
        <meta property="og:url" content="https://example.com/test/">
        <meta name="twitter:card" content="summary_large_image">
        <link rel="canonical" href="https://example.com/test/">
        <script type="application/ld+json">{"@type":"WebPage"}</script>
      </head>
    </html>
  `;
  const seo = extractSeoSignals(html, "https://example.com/test/");
  assert.equal(seo.title, "Test Page");
  assert.equal(seo.description, "Desc text");
  assert.equal(seo.robots, "index,follow");
  assert.equal(seo.indexable, true);
  assert.equal(seo.canonical, "https://example.com/test/");
  assert.equal(seo.canonicalOk, true);
  assert.equal(seo.ogTitle, "OG Test Page");
  assert.equal(seo.ogDescription, "OG Desc");
  assert.equal(seo.ogUrl, "https://example.com/test/");
  assert.equal(seo.twitterCard, "summary_large_image");
  assert.equal(seo.jsonLdCount, 1);
});

test("parseJsonLdBlocks tolerates broken json", () => {
  const html = `<script type="application/ld+json">{bad}</script>`;
  const blocks = parseJsonLdBlocks(html);
  assert.equal(blocks.length, 1);
  assert.equal(Boolean(blocks[0]._invalidJsonLd), true);
});
