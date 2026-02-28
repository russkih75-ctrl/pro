# Deploy Toolkit

## Server Audit

This folder now includes an automated server audit runner:

- Detects active WordPress docroot on FTP.
- Builds deployment drift report for critical SEO files.
- Performs HTTP endpoint checks and extracts SEO signals.

### Run

1. Configure FTP variables in `deploy/.env` or `automation/wp_autopost/.env`.
2. Install dependencies:
   - `npm install`
3. Run audit:
   - `npm run audit:server`
4. Run tests:
   - `npm test`

### Optional: HTTP-only mode (no FTP)

If FTP access is temporarily unavailable, run audit in HTTP-only mode:

- PowerShell: `$env:AUDIT_SKIP_FTP='1'; npm run audit:server`

This still generates HTTP and benchmark-style SEO reports, while skipping docroot/drift checks.

### Outputs

Reports are written to `deploy/reports/`:

- `docroot-detection.md`
- `deployment-drift.md`
- `http-audit.md`
- `gap-analysis-yandex-2026.md`