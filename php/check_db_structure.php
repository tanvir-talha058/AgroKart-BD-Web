<?php
// File: php/check_db_structure.php
// This file checks the database structure to help with debugging

require_once '../includes/db_connect.php';

echo "<h1>Database Structure Check</h1>";

// Check users table
echo "<h2>Users Table Structure</h2>";
$result = $conn->query("DESCRIBE users");

if ($result === false) {
    echo "<p>Error: Could not get table structure - " . $conn->error . "</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Field"] . "</td>";
        echo "<td>" . $row["Type"] . "</td>";
        echo "<td>" . $row["Null"] . "</td>";
        echo "<td>" . $row["Key"] . "</td>";
        echo "<td>" . $row["Default"] . "</td>";
        echo "<td>" . $row["Extra"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}

// Sample query to check data
echo "<h2>Sample User Data</h2>";
$userData = $conn->query("SELECT * FROM users LIMIT 1");

if ($userData->num_rows > 0) {
    $user = $userData->fetch_assoc();
    echo "<h3>First User Record Fields</h3>";
    echo "<ul>";
    foreach ($user as $key => $value) {
        if ($key == 'password') {
            echo "<li>" . $key . ": [HIDDEN]</li>";
        } else {
            echo "<li>" . $key . ": " . $value . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>No user records found</p>";
}

// Check if specific columns exist
echo "<h2>Important Columns Check</h2>";
$columnsToCheck = ['id', 'user_id', 'google_id', 'email', 'name', 'profile_image_path', 'created_at'];
echo "<ul>";

foreach ($columnsToCheck as $column) {
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
    if ($columnCheck->num_rows > 0) {
        echo "<li style='color:green'>✓ Column '$column' exists</li>";
    } else {
        echo "<li style='color:red'>✗ Column '$column' does NOT exist</li>";
    }
}

echo "</ul>";

echo "<p><a href='../login.php'>Back to Login</a></p>";
