# Installer Baseline 0.2.0

The first installer is CLI-only by design. A public web installer is deferred until one-time setup tokens,
host validation, CSRF protection, rate limiting, lock semantics, and safe filesystem permissions are implemented.

## Command
`php artisan marketplace:install`

The command:
1. refuses to run if `storage/app/installed.lock` exists;
2. verifies `APP_KEY`;
3. verifies database connectivity;
4. runs migrations;
5. optionally prompts for the initial administrator;
6. creates the lock file after successful completion.

The command is a baseline and still needs a tested recovery workflow, robust rollback handling, and automated integration tests before production use.
