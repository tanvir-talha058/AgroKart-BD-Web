<?php
// FILE: /php/ensure_order_columns.php
// This script ensures all required columns exist in the orders table
// Run this script once during setup or when you need to add new columns

// Determine the correct path to db_connect.php
$current_dir = dirname(__FILE__);
$root_dir = dirname($current_dir);
require_once $root_dir . '/includes/db_connect.php';

// Function to check if a column exists
function columnExists($conn, $table, $column)
{
    $result = $conn->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
    return $result->num_rows > 0;
}

// Add delivered_at column if it doesn't exist
if (!columnExists($conn, 'orders', 'delivered_at')) {
    $conn->query("ALTER TABLE orders ADD COLUMN delivered_at TIMESTAMP NULL AFTER status");
    echo "Added delivered_at column to orders table.<br>";
} else {
    echo "delivered_at column already exists.<br>";
}

// Add delivery_option column if it doesn't exist
if (!columnExists($conn, 'orders', 'delivery_option')) {
    $conn->query("ALTER TABLE orders ADD COLUMN delivery_option VARCHAR(20) DEFAULT 'standard' AFTER notes");
    echo "Added delivery_option column to orders table.<br>";
} else {
    echo "delivery_option column already exists.<br>";
}

// Add delivery_fee column if it doesn't exist
if (!columnExists($conn, 'orders', 'delivery_fee')) {
    $conn->query("ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10,2) DEFAULT 0.00 AFTER delivery_option");
    echo "Added delivery_fee column to orders table.<br>";
} else {
    echo "delivery_fee column already exists.<br>";
}

echo "<p>Orders table structure check complete.</p>";
echo "<p><a href='../orders.php'>Return to Orders</a></p>";

$conn->close();
