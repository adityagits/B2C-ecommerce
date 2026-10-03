-- Migration: invoices, payments, shipments, order timeline, API keys, credentials.
-- For databases created from the first version of schema.sql:
--   mysql -u root -p b2c_ecommerce < database/migrations/001_billing_shipping_keys.sql
CREATE TABLE IF NOT EXISTS invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL UNIQUE,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    billing_name VARCHAR(100) NOT NULL,
    billing_address VARCHAR(400) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
    tax DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('unpaid','paid','void') NOT NULL DEFAULT 'unpaid',
    issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    transaction_id VARCHAR(40) NOT NULL UNIQUE,
    method VARCHAR(30) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    paid_at TIMESTAMP NULL DEFAULT NULL,
    refunded_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payments_order (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shipments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL UNIQUE,
    carrier VARCHAR(60) NULL,
    tracking_number VARCHAR(100) NULL,
    status ENUM('processing','shipped','in_transit','out_for_delivery','delivered','returned','cancelled') NOT NULL DEFAULT 'processing',
    estimated_delivery DATE NULL,
    shipped_at TIMESTAMP NULL DEFAULT NULL,
    delivered_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    type VARCHAR(20) NOT NULL,
    message VARCHAR(255) NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_events_order (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    key_prefix CHAR(8) NOT NULL UNIQUE,
    key_hash CHAR(64) NOT NULL,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    revoked_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS credentials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    value_enc TEXT NOT NULL,
    hint VARCHAR(8) NOT NULL DEFAULT '',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Backfill existing orders
INSERT INTO invoices (order_id, invoice_number, billing_name, billing_address, subtotal, shipping, tax, total, status, issued_at)
SELECT o.id, CONCAT('INV-', YEAR(o.created_at), '-', LPAD(o.id, 5, '0')), o.shipping_name,
       CONCAT(o.shipping_address, ', ', o.shipping_city, ' ', o.shipping_zip), o.subtotal, o.shipping, o.tax, o.total,
       CASE WHEN o.status = 'cancelled' THEN 'void' WHEN o.status IN ('paid','shipped','delivered') THEN 'paid' ELSE 'unpaid' END, o.created_at
FROM orders o WHERE NOT EXISTS (SELECT 1 FROM invoices i WHERE i.order_id = o.id);

INSERT INTO payments (order_id, transaction_id, method, amount, status, paid_at, created_at)
SELECT o.id, CONCAT('TXN-', LPAD(o.id, 12, '0')), o.payment_method, o.total,
       CASE WHEN o.status = 'cancelled' THEN 'failed' WHEN o.status IN ('paid','shipped','delivered') THEN 'paid' ELSE 'pending' END,
       CASE WHEN o.status IN ('paid','shipped','delivered') THEN o.created_at END, o.created_at
FROM orders o WHERE NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.id);

INSERT INTO shipments (order_id, status, shipped_at, delivered_at, created_at)
SELECT o.id,
       CASE o.status WHEN 'shipped' THEN 'shipped' WHEN 'delivered' THEN 'delivered' WHEN 'cancelled' THEN 'cancelled' ELSE 'processing' END,
       CASE WHEN o.status IN ('shipped','delivered') THEN o.created_at END,
       CASE WHEN o.status = 'delivered' THEN o.created_at END, o.created_at
FROM orders o WHERE NOT EXISTS (SELECT 1 FROM shipments s WHERE s.order_id = o.id);

INSERT INTO order_events (order_id, type, message, created_at)
SELECT o.id, 'order', 'Order placed', o.created_at FROM orders o
WHERE NOT EXISTS (SELECT 1 FROM order_events e WHERE e.order_id = o.id);
