<?php
// Determine the correct path to db_connect.php
$current_dir = dirname(__FILE__);
$root_dir = dirname($current_dir);
require_once $root_dir . '/includes/db_connect.php';

// Add delivery_option and delivery_fee columns to orders table
$sql = "ALTER TABLE orders 
        ADD COLUMN delivery_option VARCHAR(20) DEFAULT 'standard' AFTER notes,
        ADD COLUMN delivery_fee DECIMAL(10,2) DEFAULT 0.00 AFTER delivery_option";

if ($conn->query($sql) === TRUE) {
    echo "Delivery columns added successfully";
} else {
    echo "Error adding delivery columns: " . $conn->error;
}

$conn->close();
