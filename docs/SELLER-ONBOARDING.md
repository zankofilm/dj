# Seller Onboarding Foundation 0.3.0

## Implemented baseline
- Seller application record linked to its owner account
- Application statuses: draft, submitted, under_review, approved, rejected, changes_requested
- Seller document metadata table (private storage path only)
- Review decision endpoint protected by `sellers.review` permission
- Mandatory reason for rejection or change request
- Audit log records for application submission and review decisions
- Seller application UI strings in Persian, Sorani Kurdish (`ku`), and English

## Important limitations
- Authentication UI and password reset workflow are not yet implemented in this milestone; routes require Laravel's `auth` middleware and an existing authenticated session.
- Document upload and identity verification are not yet implemented.
- Seller legal verification rules depend on the operating jurisdiction and must be configured before production.
- The initial authorization checks are a foundation; a full policy/role administration interface and security tests are still required.
- This code has not been exercised against a live hosting account or production database.
