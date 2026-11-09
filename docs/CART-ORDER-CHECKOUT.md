# Cart, Split Orders and Checkout Foundation 0.5.0

## Implemented
- Authenticated persistent carts
- Cart items keyed by product variant
- Mixed currencies are rejected in one cart
- Checkout snapshots customer delivery details
- One parent order is created per checkout
- One seller order is created per seller in the parent order
- Order item snapshots preserve product title, SKU, price, currency, product type, condition and variant options
- Inventory rows are locked during checkout
- Stock reservation and inventory movement are written in the same database transaction
- Cart is converted only after order and reservation writes succeed
- Order lookup is scoped to the owning customer

## Financial and inventory rules
- Prices use integer minor currency units; no floating point arithmetic.
- Current checkout does not calculate tax, shipping, discount, marketplace commission, or payment fees.
- Payment capture is not implemented. Orders remain `pending_payment`.
- Inventory reservation is atomic for tracked variants. Untracked variants bypass stock reservation.
- A payment expiry/release workflow is still required. Until it exists, pending orders can retain reservations.
- Product/variant price and publication data are revalidated server-side at checkout.
- The application must use InnoDB and a database engine supporting row-level locks for the intended concurrency behavior.

## Known limitations
- Cart quantity update UI is not implemented; add-to-cart and remove are implemented.
- No guest cart or cart merge.
- No shipping rate engine, address book, tax calculation, coupon engine, or payment gateway.
- No idempotency key for checkout submission yet; duplicate browser submissions need a dedicated idempotency guard before production.
- No automated integration/concurrency tests have been run in a real MySQL environment.
- Do not accept live orders or payments using this milestone.
