-- Create the seller_hidden_customers table
CREATE TABLE IF NOT EXISTS seller_hidden_customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    customer_id INT NOT NULL,
    hidden_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_seller_customer (seller_id, customer_id),
    FOREIGN KEY (seller_id) REFERENCES users (id),
    FOREIGN KEY (customer_id) REFERENCES users (id)
);

-- Add an index for faster queries
CREATE INDEX idx_seller_hidden_customers ON seller_hidden_customers (seller_id, customer_id);

-- This table allows sellers to hide customers from their view without deleting the actual user account
-- When a seller "deletes" a customer, we're actually just hiding them from that seller's view