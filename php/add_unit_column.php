<?php
// FILE: /php/add_unit_column.php
require_once '../includes/db_connect.php';

// Check if the script is run by an admin or seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    echo "Unauthorized access";
    exit;
}

// SQL to add unit column if it doesn't exist
$check_column_sql = "SHOW COLUMNS FROM `products` LIKE 'unit'";
$result = $conn->query($check_column_sql);

if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $add_column_sql = "ALTER TABLE `products` 
                       ADD COLUMN `unit` VARCHAR(10) NOT NULL DEFAULT 'kg' 
                       AFTER `price`";

    if ($conn->query($add_column_sql) === TRUE) {
        echo "Unit column added successfully!";
    } else {
        echo "Error adding unit column: " . $conn->error;
    }
} else {
    echo "Unit column already exists.";
}

// Close connection
$conn->close();
