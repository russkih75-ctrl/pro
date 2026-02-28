import test from "node:test";
import assert from "node:assert/strict";
import { buildDriftRows, classifyFile, summarizeDrift } from "./lib/drift.mjs";

test("classifyFile returns missing_on_server", () => {
  const status = classifyFile({ size: 10, sha256: "a" }, null);
  assert.equal(status, "missing_on_server");
});

test("classifyFile returns match by hash", () => {
  const status = classifyFile({ size: 10, sha256: "a" }, { size: 10, sha256: "a" });
  assert.equal(status, "match");
});

test("buildDriftRows and summarizeDrift aggregate statuses", () => {
  const rows = buildDriftRows(
    {
      "a.php": { size: 1, sha256: "1" },
      "b.php": { size: 2, sha256: "2" },
    },
    {
      "a.php": { size: 1, sha256: "1" },
    }
  );
  const summary = summarizeDrift(rows);
  assert.equal(summary.total, 2);
  assert.equal(summary.match, 1);
  assert.equal(summary.missing_on_server, 1);
});
