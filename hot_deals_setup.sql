-- Create hot_deals table for AgroKartBD
CREATE TABLE IF NOT EXISTS hot_deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    original_price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) NOT NULL,
    discount_percentage INT NOT NULL,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date)
);

-- Add some sample hot deals (optional - only if products exist)
INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage) 
SELECT id, price, ROUND(price * 0.8, 2), 20 FROM products WHERE category = 'Vegetable' LIMIT 3;

INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage) 
SELECT id, price, ROUND(price * 0.75, 2), 25 FROM products WHERE category = 'Fruit' LIMIT 2;

INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage) 
SELECT id, price, ROUND(price * 0.7, 2), 30 FROM products WHERE stock > 10 LIMIT 2;
