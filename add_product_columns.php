<?php
// Add quantity column to products table
require_once 'includes/db_connect.php';

// Check if quantity column already exists
$checkQuery = "SHOW COLUMNS FROM products LIKE 'quantity'";
$checkResult = $conn->query($checkQuery);

if ($checkResult->num_rows == 0) {
    // Add quantity column
    $alterQuery = "ALTER TABLE products ADD COLUMN quantity decimal(10,2) NOT NULL DEFAULT 1.0 AFTER unit";

    if ($conn->query($alterQuery)) {
        echo "<p>Successfully added quantity column to products table</p>";
    } else {
        echo "<p>Error adding quantity column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Quantity column already exists in products table</p>";
}

// Check if display_unit column already exists
$checkQuery = "SHOW COLUMNS FROM products LIKE 'display_unit'";
$checkResult = $conn->query($checkQuery);

if ($checkResult->num_rows == 0) {
    // Add display_unit column
    $alterQuery = "ALTER TABLE products ADD COLUMN display_unit varchar(20) AFTER quantity";

    if ($conn->query($alterQuery)) {
        echo "<p>Successfully added display_unit column to products table</p>";

        // Update existing products with default display_unit values
        $updateQuery = "UPDATE products SET display_unit = CONCAT(quantity, ' ', unit)";
        if ($conn->query($updateQuery)) {
            echo "<p>Updated existing products with display_unit values</p>";
        } else {
            echo "<p>Error updating display_unit for existing products: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Error adding display_unit column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Display unit column already exists in products table</p>";
}

echo "<p><a href='products.php'>Return to Products</a></p>";
