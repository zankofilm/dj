# Security Baseline

- `APP_DEBUG=false` in production.
- HTTPS required for authentication, checkout, admin, and installer.
- CSRF protection for browser sessions.
- Rate limits on authentication, password reset, checkout, and upload endpoints.
- Uploaded artifacts stored outside public web root and never executed in place.
- Package upload starts in quarantine; no automatic activation.
- Verify MIME, size, archive paths, manifest schema, hashes, signature, compatibility, and permissions before install.
- Enforce authorization server-side for every administrative action.
- Protect `.env`, source, vendor, storage, backups, and logs from web access.
- Use integer minor currency units; never floating point for money.
- Financial posting must be atomic, balanced per currency, idempotent, append-only, and auditable.
- Do not claim payment success based solely on browser redirects; verify provider server-side.
