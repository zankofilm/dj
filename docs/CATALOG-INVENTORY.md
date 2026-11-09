# Catalog and Inventory Foundation 0.4.0

## Added domain structures
- Hierarchical categories with localized JSON labels and sort order
- Products linked to one seller and optional category
- Product type: physical, digital, service
- Condition: new, used, refurbished
- Localized product title/description snapshots (`fa`, `ku`, `en`)
- Product variants with SKU, barcode, option values and variant-level prices
- Product media metadata with localized alt text
- Inventory balance row per variant
- Append-only inventory movement records
- Seller product submission enters `pending_review`
- Seller product creation is limited to the authenticated user's approved seller account
- Audit event for product creation

## Inventory invariants
- `on_hand` and `reserved` are non-negative unsigned quantities.
- `reserved <= on_hand`.
- All stock changes must be performed in a database transaction with row locking and a matching inventory movement.
- The current milestone defines schema only; stock adjustment, reservation, release, and fulfillment services are not implemented yet.
- Do not directly edit inventory balance values from controllers.

## Product publication
Products are created in `pending_review`. Public catalog queries must only expose products with status `published` and an approved seller. The review/publish workflow is a subsequent milestone.

## Important limitations
- Product image upload is not yet implemented; only metadata schema exists.
- Variant creation/editing UI is not yet implemented.
- Stock mutation and concurrency-safe reservation are not yet implemented.
- Category management UI and product moderation UI are not yet implemented.
- This milestone requires integration tests and live environment validation before production.
