USE joyeria;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(200) NOT NULL
) ENGINE=InnoDB;

INSERT INTO categories (name, description) VALUES
    ('artesanal', 'Piezas elaboradas por manos artesanas'),
    ('industrial', 'Piezas de fabricación industrial');

CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    address VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB;

INSERT INTO customers (id, user_id, phone, address, created_at)
SELECT id, id, phone, address, created_at FROM users WHERE `role` = 'customer';

CREATE TABLE sellers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    hired_at DATE NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    CONSTRAINT fk_sellers_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB;

INSERT INTO sellers (id, user_id, phone, hired_at, status)
SELECT id, id, phone, DATE(created_at), IF(is_active = 1, 'active', 'inactive')
FROM users WHERE `role` = 'seller';

CREATE TABLE payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

INSERT INTO payment_methods (name) VALUES ('debit'), ('credit'), ('deposit');

ALTER TABLE products
    ADD COLUMN category_id BIGINT UNSIGNED NULL AFTER id;

UPDATE products p
JOIN categories c ON c.name = p.category
SET p.category_id = c.id;

ALTER TABLE products
    DROP INDEX idx_products_category_active,
    MODIFY category_id BIGINT UNSIGNED NOT NULL,
    ADD INDEX idx_products_category_active (category_id, is_active),
    ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id),
    DROP COLUMN category;

CREATE TABLE carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    state ENUM('active', 'finalized') NOT NULL DEFAULT 'active',
    INDEX idx_carts_customer_state (customer_id, state),
    CONSTRAINT fk_carts_customer FOREIGN KEY (customer_id) REFERENCES customers (id)
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_cart_product (cart_id, product_id),
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts (id),
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT chk_cart_items_quantity CHECK (quantity > 0)
) ENGINE=InnoDB;

ALTER TABLE sales
    ADD COLUMN payment_method_id BIGINT UNSIGNED NULL AFTER seller_id;

UPDATE sales s
JOIN payment_methods m ON m.name = s.payment_method
SET s.payment_method_id = m.id;

ALTER TABLE sales
    DROP FOREIGN KEY fk_sales_customer,
    DROP FOREIGN KEY fk_sales_seller,
    DROP COLUMN payment_method,
    MODIFY customer_id BIGINT UNSIGNED NOT NULL,
    MODIFY payment_method_id BIGINT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_sales_customer FOREIGN KEY (customer_id) REFERENCES customers (id),
    ADD CONSTRAINT fk_sales_seller FOREIGN KEY (seller_id) REFERENCES sellers (id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_sales_payment_method FOREIGN KEY (payment_method_id) REFERENCES payment_methods (id);

ALTER TABLE customer_credit
    DROP FOREIGN KEY fk_credit_customer;

ALTER TABLE customer_credit
    ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE FIRST,
    ADD COLUMN state ENUM('active', 'suspended', 'settled') NOT NULL DEFAULT 'active';

ALTER TABLE customer_credit
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (id),
    ADD UNIQUE KEY uq_customer_credit_customer (customer_id),
    ADD CONSTRAINT fk_credit_customer FOREIGN KEY (customer_id) REFERENCES customers (id);

ALTER TABLE credit_payments
    DROP FOREIGN KEY fk_credit_payments_customer,
    ADD CONSTRAINT fk_credit_payments_customer FOREIGN KEY (customer_id) REFERENCES customers (id);

ALTER TABLE users
    DROP COLUMN phone,
    DROP COLUMN address;
