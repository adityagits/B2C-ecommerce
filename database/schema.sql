-- ShopEasy schema + demo data.  Usage: mysql -u root -p < database/schema.sql
CREATE DATABASE IF NOT EXISTS b2c_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE b2c_ecommerce;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS credentials, api_keys, order_events, shipments, payments, invoices, reviews, order_items, orders, products, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_products_category (category_id),
    FULLTEXT KEY ft_products (name, description)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
    tax DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(30) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_zip VARCHAR(20) NOT NULL,
    shipping_phone VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review (product_id, user_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Billing, fulfilment and key management
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

-- Demo accounts: admin@example.com / admin123  and  customer@example.com / customer123
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@example.com', '$2y$10$dpgsBiBJKMg4NuH4qmi3/e2q2.p93ajNK/w6HemTSuWLSS666iLk.', 'admin'),
('Demo Customer', 'customer@example.com', '$2y$10$X0dXNkeQ0IM5wKNqw00Qnu8ArEHJT9SrOKZArXv0FRx4unIWCDv9u', 'customer');

INSERT INTO categories (name) VALUES ('Electronics'), ('Clothing'), ('Home & Kitchen'), ('Books'), ('Sports');

INSERT INTO products (category_id, name, description, price, stock, featured) VALUES
(1, 'Wireless Headphones', 'Over-ear Bluetooth headphones with active noise cancelling and 30-hour battery life.', 89.99, 25, 1),
(1, 'Smart Watch', 'Fitness tracking, heart-rate monitor, GPS and a bright AMOLED display.', 129.00, 15, 1),
(1, 'Portable Bluetooth Speaker', 'Waterproof speaker with deep bass and 12 hours of playtime.', 39.50, 40, 0),
(1, 'USB-C Fast Charger', '65W GaN charger that powers a laptop and a phone at the same time.', 29.99, 60, 0),
(2, 'Classic Cotton T-Shirt', 'Soft, breathable 100% cotton crew-neck tee. Available in many colours.', 14.99, 100, 0),
(2, 'Denim Jacket', 'Timeless medium-wash denim jacket with a comfortable relaxed fit.', 59.00, 20, 1),
(2, 'Running Sneakers', 'Lightweight cushioned sneakers built for daily training and long runs.', 74.95, 30, 1),
(3, 'Stainless Steel Cookware Set', '10-piece non-stick cookware set, induction compatible and dishwasher safe.', 149.00, 8, 0),
(3, 'Electric Kettle', '1.7L fast-boil kettle with auto shut-off and boil-dry protection.', 34.99, 35, 0),
(3, 'Scented Soy Candle', 'Hand-poured soy wax candle with a calming lavender and vanilla scent.', 12.50, 3, 0),
(4, 'The Pragmatic Programmer', 'Classic guide to software craftsmanship, from journeyman to master.', 42.00, 22, 0),
(4, 'Atomic Habits', 'An easy and proven way to build good habits and break bad ones.', 16.99, 50, 1),
(5, 'Yoga Mat', 'Extra-thick non-slip yoga mat with carrying strap.', 24.99, 45, 0),
(5, 'Adjustable Dumbbell Set', 'Space-saving dumbbells adjustable from 2 to 24 kg.', 199.00, 6, 0);
