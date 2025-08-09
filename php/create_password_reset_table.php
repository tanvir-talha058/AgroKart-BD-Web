<?php
// FILE: php/create_password_reset_table.php

// Include database connection
require_once 'f:/AgroKart-BD-Web/includes/db_connect.php';

// SQL to create the password_reset_tokens table
$sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    token VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Table 'password_reset_tokens' created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

// Close the connection
$conn->close();
