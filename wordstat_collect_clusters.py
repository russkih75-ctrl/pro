import json
import time
from pathlib import Path


"""
Offline helper to collect Wordstat clusters.

This script is a placeholder: in Cursor we already have MCP tool access, but keeping seeds+logic
in repo makes it easy to reproduce later (CI, local automation).

Expected usage (future):
- read wordstat_clusters_seed.json
- call Wordstat API (via our MCP runner or direct API) to fetch top requests
- save results into wordstat_clusters_out/*.json

For now: it just validates the seed file exists and prints planned calls.
"""


def main():
    seed_path = Path("wordstat_clusters_seed.json")
    data = json.loads(seed_path.read_text(encoding="utf-8"))

    regions = list(data["regions"].values())
    devices = data.get("devices", ["all"])

    calls = []
    for group, seeds in data["seeds"].items():
        for phrase in seeds:
            calls.append({"phrase": phrase, "numPhrases": 20, "regions": regions, "devices": devices})

    out = {
        "generated_at": int(time.time()),
        "calls": calls,
    }
    print(json.dumps(out, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()

