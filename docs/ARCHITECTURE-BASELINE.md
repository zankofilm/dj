# Architecture Baseline 0.2.0

## Deployment target
Linux shared hosting first, with a portability path to VPS/dedicated infrastructure.

## Initial stack
- PHP 8.2+ and Laravel 12
- Vue 3 + Vite-built assets
- MySQL 8 / compatible MariaDB
- Database-backed cache, sessions and queue for shared hosting
- Local private storage initially
- REST API
- Modular monolith

## Portability adapters
- Cache: file/database -> Valkey
- Queue: database + cron -> RabbitMQ
- Search: database full-text -> OpenSearch
- Storage: local -> S3-compatible object storage
- Widget runtime: restricted trusted components -> WASM/container isolation on VPS

## Locale
`fa`, `ku`, `en`; `fa` and `ku` are RTL, `en` is LTR.

## Financial integrity
Money is represented in integer minor units with an explicit ISO currency. Posted ledger entries must be append-only at the application layer. Corrections use compensating journals. The initial migrations are only schema foundations; balanced posting services and reconciliation are not yet implemented.

## Explicit exclusions
No live PSP, seller payout, tax engine, complete order workflow, untrusted plugin execution, signed theme repository, production web installer, or production HA is claimed in this milestone.
