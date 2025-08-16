<?php
// This script updates the display_unit field for all existing products
require_once 'includes/db_connect.php';

// Update display_unit for all products
$sql = "UPDATE products SET display_unit = CONCAT(quantity, ' ', unit) WHERE 1";
if ($conn->query($sql)) {
    echo "Successfully updated display units for all products.<br>";
} else {
    echo "Error updating display units: " . $conn->error . "<br>";
}

echo "<p><a href='products.php'>Return to Products</a></p>";
