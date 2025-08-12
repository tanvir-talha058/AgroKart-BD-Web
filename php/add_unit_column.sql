-- Add unit column to products table
ALTER TABLE `products`
ADD COLUMN `unit` VARCHAR(10) NOT NULL DEFAULT 'kg' AFTER `price`;