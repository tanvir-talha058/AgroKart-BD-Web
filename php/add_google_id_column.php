<?php
// FILE: php/add_google_id_column.php
// This script adds the google_id column to the users table for Google OAuth support

require_once 'includes/db_connect.php';

echo "<h2>Adding Google ID Column to Users Table</h2>";

try {
    // Check if google_id column already exists
    $check_query = "SHOW COLUMNS FROM users LIKE 'google_id'";
    $result = $conn->query($check_query);

    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ google_id column already exists in users table.</p>";
    } else {
        // Add google_id column
        $alter_query = "ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email";

        if ($conn->query($alter_query) === TRUE) {
            echo "<p style='color: green;'>✅ Successfully added google_id column to users table.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding google_id column: " . $conn->error . "</p>";
        }
    }

    // Also check if created_at column exists (useful for Google OAuth users)
    $check_created_at = "SHOW COLUMNS FROM users LIKE 'created_at'";
    $created_at_result = $conn->query($check_created_at);

    if ($created_at_result->num_rows == 0) {
        $add_created_at = "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        if ($conn->query($add_created_at) === TRUE) {
            echo "<p style='color: green;'>✅ Successfully added created_at column to users table.</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Could not add created_at column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ created_at column already exists in users table.</p>";
    }

    echo "<br><p><strong>Database is ready for Google OAuth integration!</strong></p>";
    echo "<p><a href='../login.php'>← Back to Login Page</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
