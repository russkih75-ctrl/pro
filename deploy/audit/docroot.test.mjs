import test from "node:test";
import assert from "node:assert/strict";
import { isWordPressRoot, pickBestDocroot, scoreWordPressRoot } from "./lib/docroot.mjs";

test("scoreWordPressRoot counts markers", () => {
  const score = scoreWordPressRoot(["wp-config.php", "wp-content", "random"]);
  assert.equal(score, 2);
});

test("isWordPressRoot requires at least two markers", () => {
  assert.equal(isWordPressRoot(["wp-content", "wp-admin"]), true);
  assert.equal(isWordPressRoot(["wp-content"]), false);
});

test("pickBestDocroot selects highest score candidate", () => {
  const best = pickBestDocroot({
    "/": ["tmp"],
    "/public_html": ["wp-content", "wp-admin"],
    "/kv/public_html": ["wp-content", "wp-admin", "wp-includes", "wp-config.php"],
  });
  assert.deepEqual(best, { root: "/kv/public_html", score: 4 });
});
