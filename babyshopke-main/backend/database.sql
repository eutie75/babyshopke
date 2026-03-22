-- Baby Shop KE MySQL schema (PHP 8 + PDO)
-- Compatible with XAMPP MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS babyshopke CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE babyshopke;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS mpesa_transactions;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS wishlist_items;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS children;
DROP TABLE IF EXISTS family_members;
DROP TABLE IF EXISTS families;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE families (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_user_id INT UNSIGNED NOT NULL,
    family_name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_families_owner
        FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE family_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    member_role ENUM('owner', 'parent', 'guardian') NOT NULL DEFAULT 'parent',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_family_member (family_id, user_id),
    CONSTRAINT fk_family_members_family
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE,
    CONSTRAINT fk_family_members_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE children (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_id INT UNSIGNED NOT NULL,
    child_name VARCHAR(120) NOT NULL,
    dob DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_children_family
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    category VARCHAR(80) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    age_min_months INT UNSIGNED NOT NULL DEFAULT 0,
    age_max_months INT UNSIGNED NOT NULL DEFAULT 48,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_products_category (category),
    INDEX idx_products_age (age_min_months, age_max_months)
) ENGINE=InnoDB;

CREATE TABLE wishlists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlists_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE wishlist_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wishlist_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_wishlist_product (wishlist_id, product_id),
    CONSTRAINT fk_wishlist_items_wishlist
        FOREIGN KEY (wishlist_id) REFERENCES wishlists(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_items_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    qty INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cart_product (cart_id, product_id),
    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── orders ───────────────────────────────────────────────────────────────────
-- payment_method now includes MPESA (real STK Push) alongside COD
CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    family_id INT UNSIGNED NULL,
    child_id INT UNSIGNED NULL,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('MPESA', 'COD') NOT NULL,
    delivery_option ENUM('delivery', 'pickup') NOT NULL DEFAULT 'delivery',
    -- pending   → order created, awaiting payment
    -- awaiting_payment → STK push sent, waiting for M-Pesa callback
    -- paid      → payment confirmed
    -- shipped   → dispatched
    -- delivered → received by customer
    status ENUM('pending', 'awaiting_payment', 'paid', 'shipped', 'delivered') NOT NULL DEFAULT 'pending',
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_orders_family
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE SET NULL,
    CONSTRAINT fk_orders_child
        FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─── mpesa_transactions ───────────────────────────────────────────────────────
-- Tracks every STK Push attempt and Safaricom callback result
CREATE TABLE mpesa_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    -- The CheckoutRequestID returned by Daraja after STK Push
    checkout_request_id VARCHAR(100) NOT NULL UNIQUE,
    -- The MerchantRequestID returned by Daraja
    merchant_request_id VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    -- pending → STK sent; success → paid; failed → declined/cancelled
    status ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
    -- Populated from callback
    mpesa_receipt VARCHAR(30) NULL,
    result_code INT NULL,
    result_desc TEXT NULL,
    callback_raw JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mpesa_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    qty INT UNSIGNED NOT NULL,
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ─── Seed data ────────────────────────────────────────────────────────────────
-- Admin password: Admin@123
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Admin User', 'admin@babyshopke.co.ke', '$2y$12$LJ3m4ys3Gzf0Ga2VEjKIiOjJN1QF.r6.x6YfBqKq9jjLkRjhDu.tu', 'admin');

-- User password: User@123
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Jane Wanjiku', 'jane@example.com', '$2y$12$XuQ5z8gVfMkJ.yqU3E.0a.Vk0v4HsZ2kJYp1b2W6rD1mNpX5m3xXe', 'user');

-- Seed products (12)
INSERT INTO products (name, description, price, stock, category, image_url, age_min_months, age_max_months) VALUES
('Pampers Premium Care Size 2', 'Soft and absorbent diapers for 3-8kg babies.', 1450.00, 120, 'Diapers & Wipes', 'https://placehold.co/600x600/E0F7FA/1F2933?text=Pampers+S2', 0, 6),
('Huggies Dry Comfort Size 4', 'Long-lasting dryness diapers for active babies.', 1690.00, 90, 'Diapers & Wipes', 'https://placehold.co/600x600/E0F7FA/1F2933?text=Huggies+S4', 6, 18),
('Baby Wet Wipes 80 Pack', 'Gentle, alcohol-free wipes for daily use.', 380.00, 240, 'Diapers & Wipes', 'https://placehold.co/600x600/E0F7FA/1F2933?text=Wet+Wipes', 0, 48),
('Philips Avent Anti-Colic Bottle', '260ml BPA-free feeding bottle with anti-colic valve.', 1250.00, 70, 'Feeding', 'https://placehold.co/600x600/FFF3E0/1F2933?text=Avent+Bottle', 0, 12),
('Silicone Feeding Set', 'Suction bowl, spoon and bib for weaning babies.', 1650.00, 45, 'Feeding', 'https://placehold.co/600x600/FFF3E0/1F2933?text=Feeding+Set', 6, 36),
('Sippy Learner Cup', 'Spill-proof cup with easy grip handles.', 920.00, 80, 'Feeding', 'https://placehold.co/600x600/FFF3E0/1F2933?text=Learner+Cup', 6, 24),
('Stacking Rings Toy', 'Colorful stacking toy for motor skill development.', 850.00, 65, 'Toys', 'https://placehold.co/600x600/E8F5E9/1F2933?text=Stacking+Rings', 6, 18),
('Soft Plush Teddy', 'Hypoallergenic plush teddy for comfort and play.', 1150.00, 58, 'Toys', 'https://placehold.co/600x600/E8F5E9/1F2933?text=Plush+Teddy', 0, 48),
('Activity Shape Sorter', 'Interactive learning toy with shape blocks.', 2100.00, 36, 'Toys', 'https://placehold.co/600x600/E8F5E9/1F2933?text=Shape+Sorter', 12, 36),
('Cotton Onesie 3 Pack', 'Breathable cotton onesies for daily wear.', 1850.00, 72, 'Clothing', 'https://placehold.co/600x600/FCE4EC/1F2933?text=Onesie+3PK', 0, 12),
('Baby Hoodie Set', 'Warm 2-piece hoodie and jogger set.', 2400.00, 40, 'Clothing', 'https://placehold.co/600x600/FCE4EC/1F2933?text=Hoodie+Set', 6, 24),
('Safari Romper', 'Cute safari themed romper with snap closure.', 1390.00, 50, 'Clothing', 'https://placehold.co/600x600/FCE4EC/1F2933?text=Safari+Romper', 3, 18);
