# AGENTS.md

## Cursor Cloud specific instructions

### Project overview

This is a **WordPress website** (`kvadratyra.ru`) — a construction/roofing services site for the Voronezh region. There is no local WordPress/MySQL/Apache stack; the site is deployed to remote Beget hosting via FTP.

Locally buildable/testable components:

| Component | Directory | Key commands |
|---|---|---|
| Theme frontend (Vite) | `wp-content/themes/kvadratyra-theme/` | `npm run dev`, `npm run build` |
| Deploy toolkit + audit | `deploy/` | `npm test` (8 unit tests), `npm run audit:server` (needs FTP creds) |
| WP autopost bot | `automation/wp_autopost/` | Python + Playwright (needs WP credentials) |

### Running services

- **Vite dev server**: `cd wp-content/themes/kvadratyra-theme && npm run dev` — starts on `http://localhost:5173/`. This is the main dev-loop command for theme JS/CSS work.
- **Vite build**: `npm run build` in the same directory — produces `assets/dist/main.js` and `assets/dist/style.css`.
- **Deploy tests**: `cd deploy && npm test` — runs 8 Node.js built-in test runner tests (`node --test audit/*.test.mjs`). No external services needed.

### Lint

No ESLint/Prettier/Stylelint/PHPCS is configured in this repo. The only automated quality check is `npm test` in `deploy/`.

### Deployment

See `DEPLOY.md` for manual FTP deploy instructions and `deploy/README.md` for the automated audit toolkit. FTP credentials are required for deploy and server audit (configured in `deploy/.env`).

### Gotchas

- The Vite config sets `root` to the theme directory and outputs to `assets/dist/`. The `base: './'` setting is important for WordPress asset paths.
- Python Playwright scripts in `automation/wp_autopost/` require `playwright install chromium` after `pip install`.
- The deploy toolkit's `npm run audit:server` requires FTP credentials; to run HTTP-only checks, set `AUDIT_SKIP_FTP=1`.
