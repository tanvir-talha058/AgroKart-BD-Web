<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database Structure</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f9f6;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2e7d32;
            margin-bottom: 20px;
        }

        .success {
            background-color: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 10px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            padding: 10px;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            background-color: #2e7d32;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }

        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Database Update Tool</h1>

        <?php
        require_once 'includes/db_connect.php';

        // Check if the user is logged in as admin or seller
        if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
            echo '<div class="error">
                <p>Error: You must be logged in as a seller to use this tool.</p>
                <a href="login.php" class="button">Go to Login</a>
            </div>';
            exit;
        }

        // Function to check if column exists
        function columnExists($conn, $table, $column)
        {
            $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
            $result = $conn->query($sql);
            return $result->num_rows > 0;
        }

        // Check if unit column already exists
        if (columnExists($conn, 'products', 'unit')) {
            echo '<div class="success">
                <p>The "unit" column already exists in the products table.</p>
            </div>';
        } else {
            // Add the unit column
            $sql = "ALTER TABLE `products` ADD COLUMN `unit` VARCHAR(10) NOT NULL DEFAULT 'kg' AFTER `price`";

            if ($conn->query($sql) === TRUE) {
                echo '<div class="success">
                    <p>Successfully added "unit" column to the products table.</p>
                    <p>All existing products have been set to use "kg" as the default unit.</p>
                </div>';
            } else {
                echo '<div class="error">
                    <p>Error adding column: ' . $conn->error . '</p>
                </div>';
            }
        }

        // Show the SQL that was executed
        echo '<h3>SQL Executed:</h3>';
        echo '<pre>ALTER TABLE `products` ADD COLUMN `unit` VARCHAR(10) NOT NULL DEFAULT \'kg\' AFTER `price`;</pre>';

        // Return to dashboard button
        echo '<a href="dashboard.php" class="button">Return to Dashboard</a>';

        // Close connection
        $conn->close();
        ?>
    </div>
</body>

</html>