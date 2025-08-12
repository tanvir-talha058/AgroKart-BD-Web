-- Add the notes column to the orders table if it doesn't exist
ALTER TABLE `orders`
ADD COLUMN IF NOT EXISTS `notes` TEXT NULL DEFAULT NULL AFTER `status`;