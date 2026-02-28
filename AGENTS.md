# AGENTS.md

## Cursor Cloud specific instructions

### Overview

This is the **kvadratyra.ru** WordPress project — a construction services website. The repo contains:

| Component | Path | Purpose |
|---|---|---|
| WordPress theme | `wp-content/themes/kvadratyra-theme/` | Custom classic PHP theme with Vite-built JS/CSS |
| MU-plugin | `wp-content/mu-plugins/kv-geo/` | GEO-matrix: virtual city/service pages, sitemaps, IndexNow |
| Deploy toolkit | `deploy/` | FTP deploy + SEO/drift audit scripts (Node.js) |
| Autopost automation | `automation/wp_autopost/` | Playwright-based WP content publisher (Python) |

WordPress itself runs on a remote Beget host — there is no local WP/MySQL instance to start.

### Running services locally

- **Theme dev server:** `npm run dev` in `wp-content/themes/kvadratyra-theme/` — starts Vite on port 5173. This only serves the JS/CSS bundle, not the full WP site.
- **Theme build:** `npm run build` in `wp-content/themes/kvadratyra-theme/` — produces `assets/dist/main.js` + `assets/dist/style.css`.
- **Deploy:** `npm run deploy` in `deploy/` — requires FTP credentials in `deploy/.env` (see `deploy/env.example`).

### Testing

- **Automated tests:** `npm test` in `deploy/` — runs 8 unit tests (docroot detection, drift analysis, HTTP/SEO audit).
- **Audit (HTTP-only, no FTP):** `AUDIT_SKIP_FTP=1 npm run audit:server` in `deploy/` — runs HTTP endpoint checks against the live site without FTP credentials.

### Python autopost

Located in `automation/wp_autopost/`. Uses a venv at `.venv/`:
```
source automation/wp_autopost/.venv/bin/activate
python automation/wp_autopost/wp_client.py articles/example.json
```
Requires WP credentials in `automation/wp_autopost/.env` (see `config.example.env`).

### Gotchas

- No linter (ESLint/Prettier) is configured in this repo; `npm run lint` is not available.
- The deploy and audit scripts require FTP credentials to fully run. Without them, use `AUDIT_SKIP_FTP=1` for HTTP-only audits.
- The `package-lock.json` files use npm (not pnpm/yarn).
- Python venv requires `python3.12-venv` system package to create. The update script handles this.
