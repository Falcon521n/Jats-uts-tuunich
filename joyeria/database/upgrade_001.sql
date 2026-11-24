USE joyeria;

ALTER TABLE users
    ADD COLUMN last_name VARCHAR(120) NULL AFTER name,
    ADD COLUMN phone VARCHAR(30) NULL AFTER password_hash,
    ADD COLUMN address TEXT NULL AFTER phone;

ALTER TABLE products
    ADD COLUMN piece_type VARCHAR(80) NOT NULL DEFAULT 'General' AFTER category,
    ADD COLUMN material VARCHAR(80) NOT NULL DEFAULT 'No especificado' AFTER piece_type,
    ADD INDEX idx_products_type_material (piece_type, material);
