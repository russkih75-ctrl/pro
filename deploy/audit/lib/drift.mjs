export function classifyFile(localMeta, serverMeta) {
  if (!localMeta && !serverMeta) return "unknown";
  if (!localMeta && serverMeta) return "server_only";
  if (localMeta && !serverMeta) return "missing_on_server";

  if (localMeta.sha256 && serverMeta.sha256 && localMeta.sha256 === serverMeta.sha256) {
    return "match";
  }

  if (Number(localMeta.size) === Number(serverMeta.size)) {
    return "size_match_hash_unknown";
  }

  return "mismatch";
}

export function buildDriftRows(localMap, serverMap, impactResolver = null) {
  const keys = new Set([
    ...Object.keys(localMap || {}),
    ...Object.keys(serverMap || {}),
  ]);

  const rows = [];
  for (const file of [...keys].sort()) {
    const localMeta = localMap?.[file] || null;
    const serverMeta = serverMap?.[file] || null;
    const status = classifyFile(localMeta, serverMeta);
    const impact = typeof impactResolver === "function" ? impactResolver(file) : "normal";

    rows.push({
      file,
      status,
      impact,
      local: localMeta,
      server: serverMeta,
    });
  }
  return rows;
}

export function summarizeDrift(rows) {
  const summary = {
    total: rows.length,
    match: 0,
    mismatch: 0,
    missing_on_server: 0,
    server_only: 0,
    size_match_hash_unknown: 0,
    unknown: 0,
  };

  for (const row of rows) {
    if (summary[row.status] === undefined) summary[row.status] = 0;
    summary[row.status] += 1;
  }

  return summary;
}
