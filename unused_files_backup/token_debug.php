<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db_connect.php';

// Get server time
$server_time = date('Y-m-d H:i:s');

// Get all password reset tokens
$tokens_sql = "SELECT prt.*, u.email FROM password_reset_tokens prt 
               JOIN users u ON prt.user_id = u.id
               ORDER BY prt.expires_at DESC";
$tokens_result = $conn->query($tokens_sql);

// Get current session email
$session_email = isset($_SESSION['forgot_email']) ? $_SESSION['forgot_email'] : 'Not set';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['test_token'])) {
        $test_user_id = $_POST['user_id'];
        $test_token = $_POST['token'];

        // Test direct database query
        $test_sql = "SELECT * FROM password_reset_tokens 
                    WHERE user_id = '$test_user_id' 
                    AND token = '$test_token' 
                    AND expires_at > '$server_time'";
        $test_result = $conn->query($test_sql);

        $test_outcome = ($test_result && $test_result->num_rows > 0) ? "VALID" : "INVALID";
        $test_message = "Token test result: $test_outcome";
    }

    if (isset($_POST['reset_token'])) {
        $reset_user_id = $_POST['user_id'];

        // Generate a new token
        $new_token = rand(100000, 999999);
        $expiry_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        // Update the token
        $update_sql = "UPDATE password_reset_tokens 
                      SET token = '$new_token', expires_at = '$expiry_time' 
                      WHERE user_id = '$reset_user_id'";
        $conn->query($update_sql);

        $reset_message = "Token reset to: $new_token (expires at $expiry_time)";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Debug Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1,
        h2 {
            color: #2c3e50;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info {
            background-color: #e8f4f8;
            padding: 10px;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .expired {
            color: #e74c3c;
        }

        .valid {
            color: #27ae60;
        }

        button {
            padding: 8px 12px;
            margin: 5px;
            cursor: pointer;
        }

        input[type="text"] {
            padding: 6px;
            width: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Password Reset Token Debug Tool</h1>

        <div class="card info">
            <h3>System Info</h3>
            <p>Server Time: <strong><?php echo $server_time; ?></strong></p>
            <p>Session Email: <strong><?php echo htmlspecialchars($session_email); ?></strong></p>
            <?php if (isset($test_message)): ?>
                <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px;">
                    <p><strong><?php echo $test_message; ?></strong></p>
                    <p>SQL: <?php echo $test_sql; ?></p>
                </div>
            <?php endif; ?>
            <?php if (isset($reset_message)): ?>
                <div style="background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px;">
                    <p><strong><?php echo $reset_message; ?></strong></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Active Password Reset Tokens</h2>

            <?php if ($tokens_result && $tokens_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Token</th>
                            <th>Expires At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $tokens_result->fetch_assoc()): ?>
                            <?php
                            $is_expired = strtotime($row['expires_at']) < strtotime($server_time);
                            $status_class = $is_expired ? 'expired' : 'valid';
                            $status_text = $is_expired ? 'Expired' : 'Valid';
                            ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo $row['token']; ?></td>
                                <td><?php echo $row['expires_at']; ?></td>
                                <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                        <input type="hidden" name="token" value="<?php echo $row['token']; ?>">
                                        <button type="submit" name="test_token">Test Token</button>
                                        <button type="submit" name="reset_token">Reset Token</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tokens found in the database.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Test Custom Token</h2>
            <form method="post">
                <p>
                    <label for="user_id">User ID:</label>
                    <input type="text" id="user_id" name="user_id" required>

                    <label for="token">Token:</label>
                    <input type="text" id="token" name="token" required>

                    <button type="submit" name="test_token">Test Token</button>
                </p>
            </form>
        </div>
    </div>
</body>

</html>