# ShopEasy — B2C e-commerce (PHP + MySQL, MVC)

A dependency-free PHP 8.1+ storefront with a small hand-written MVC core.

## Features
**Storefront:** home page (featured / new arrivals / categories), product listing with search, category filter, sorting and pagination, product detail with stock status and reviews, session cart (add / update / remove, stock-aware), registration / login / logout, checkout (shipping + payment method, demo — no real payment), order history and cancellation.

**Admin (`/admin`):** dashboard (revenue, orders, low stock), product CRUD with image upload, order list with status filter and status updates (cancelling restocks items).

**Billing & delivery:** every order automatically gets a sequential invoice (`INV-YYYY-00001`, printable / Save-as-PDF at `/orders/{id}/invoice`), a payment transaction record (`TXN-…`, status pending / paid / failed / refunded) and a shipment record (carrier, tracking number, ETA, status processing → shipped → in transit → out for delivery → delivered / returned). Customers see a delivery tracker and order history; admins manage payment and shipment per order, plus invoice and payment lists. Cash-on-delivery orders are marked paid when delivered; cancelling refunds the payment, voids the invoice, cancels the shipment and restocks.

**Key management (`/admin/keys`):** create / revoke API keys (shown once, stored hashed) for the read-only JSON API (`GET /api/products`, `/api/products/{id}`, `/api/orders`, `/api/orders/{id}` with `Authorization: Bearer sk_…`), and an encrypted vault (AES-256-GCM) for gateway / carrier / SMTP credentials, read in code with `Credential::get('NAME')`. The encryption key comes from the `APP_KEY` env var, or is generated into `storage/app.key` (git-ignored, back it up!).

**Security:** PDO prepared statements, `password_hash`, CSRF tokens on every POST, output escaping, session ID regeneration on login, validated image uploads, transactional order placement with stock checks.

## Structure
```
public/            web root (index.php front controller, assets, uploads)
app/core/          Router, Controller, Model, Database, Cart, helpers
app/controllers/   request handlers
app/models/        database access (User, Product, Category, Order)
app/views/         PHP templates (layout/, products/, cart/, admin/, ...)
app/routes.php     route table
config/config.php  DB credentials & store settings (env vars supported)
database/schema.sql  tables + demo data
database/migrations/ upgrade scripts for existing databases
storage/           generated encryption key (git-ignored)
```

## Setup
1. Create the database and demo data:
   `mysql -u root -p < database/schema.sql`
   (Upgrading a database from the first version? Run `database/migrations/001_billing_shipping_keys.sql` instead — it also back-fills invoices, payments and shipments for existing orders.)
2. Set credentials in `config/config.php` or via env vars `DB_HOST DB_PORT DB_NAME DB_USER DB_PASS`.
3. Run locally: `php -S localhost:8000 -t public public/index.php`
   (On Apache/Nginx point the document root to `public/`; `.htaccess` handles rewrites.)
4. Make sure `public/uploads/` is writable by the web server.

## Demo accounts
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | admin123 |
| Customer | customer@example.com | customer123 |

Change these passwords before any real deployment.
