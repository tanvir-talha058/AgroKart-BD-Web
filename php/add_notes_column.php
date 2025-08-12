<?php
// File: php/add_notes_column.php
require_once __DIR__ . '/../includes/db_connect.php';

// Read the SQL file
$sql = file_get_contents(__DIR__ . '/add_notes_column.sql');

// Execute the SQL statement
if ($conn->multi_query($sql)) {
    echo "Notes column added successfully to the orders table.";
} else {
    echo "Error executing SQL: " . $conn->error;
}

// Close the connection
$conn->close();
