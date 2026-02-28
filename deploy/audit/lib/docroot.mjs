export const DEFAULT_DOCROOT_CANDIDATES = [
  "/",
  "/public_html",
  "/kvadratyra.ru/public_html",
  "/kvadratyra.ru-78/public_html",
];

const WP_MARKERS = ["wp-config.php", "wp-content", "wp-admin", "wp-includes"];

export function scoreWordPressRoot(entryNames) {
  if (!Array.isArray(entryNames)) return 0;
  const set = new Set(entryNames.map((name) => String(name)));
  let score = 0;
  for (const marker of WP_MARKERS) {
    if (set.has(marker)) score += 1;
  }
  return score;
}

export function isWordPressRoot(entryNames, minScore = 2) {
  return scoreWordPressRoot(entryNames) >= minScore;
}

export function pickBestDocroot(candidateMap, minScore = 2) {
  let best = null;

  for (const [root, names] of Object.entries(candidateMap || {})) {
    const score = scoreWordPressRoot(names);
    if (score < minScore) continue;
    if (!best || score > best.score) {
      best = { root, score };
    }
  }

  return best;
}
