UPDATE users
SET `role` = 'customer', is_active = 0
WHERE `role` = 'seller';

ALTER TABLE users
    MODIFY COLUMN `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer';
