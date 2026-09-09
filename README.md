# Banglay Chinese

Laravel + Filament application for Banglay Chinese — Chinese language courses
(HSK 1–4), digital study products, free study resources, and Study-in-China
consultancy/mentorship for Bangladeshi students.

## Architecture notes

- **Canonical sales ledger:** every sale creates an `Order → OrderItem →
  Payment` record set. Received/paid/due amounts are always derived from the
  canonical `Payment` rows — never stored as independent totals on the order.
- **Legacy/domain mirrors:** `Enrollment` (courses), `DigitalOrder`
  (products) and `ServiceOrder` (services) remain the fulfillment anchors and
  are kept in sync with the canonical ledger transactionally by
  `PaymentReviewService` / `OrderMaterializer` / `CheckoutOrderWriter`.
- **Checkout:** paid courses and digital products use the unified public
  checkout (`/checkout/{type}/{slug}`); guests can buy without an account and
  an account is found-or-created by normalized email (random internal
  password; onboarding via the existing password-reset + verification emails).
  A successful paid-course purchase redirects to a short-lived **signed**
  public confirmation page — no login wall.
- **Free courses:** published courses priced at exactly `0` enroll through
  the direct free-enrollment path (`courses.enroll`), become active
  immediately, and create a zero-total canonical order **without** a payment
  row. `payment_method=free` against a paid course is rejected server-side.
- **Downloads:** digital downloads are authorized from the current canonical
  Order/Payment state and fail closed.
- **Admin:** Filament panel at `/admin`. Manual/admin sales and payments flow
  through the canonical ledger via `PaymentReviewService::recordPayment`.
- **Email:** all delivery is queued through the database queue and routed via
  stored, encrypted `EmailProvider` credentials (Brevo SMTP) with an
  `EmailLog` lifecycle; scheduled `queue:work --stop-when-empty` runs from the
  scheduler for shared hosting.
- **Seeders:** production `db:seed --force` seeds only essential data (no demo
  users/orders/content); demo/test data is restricted to `app()->isLocal()`.

## Stack

PHP 8.3+/8.4 · Laravel · Filament v4+ · MySQL (prod) / SQLite (dev/tests) ·
Vite + Tailwind CSS.

## Setup & deployment

See `DEPLOY.md` for environment setup, seeding, scheduling (cron), and
Hostinger deployment notes.

## Tests

```bash
php -d memory_limit=1G vendor/bin/phpunit
php artisan orders:parity
```
