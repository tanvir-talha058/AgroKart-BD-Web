<?php
// THIS IS A DEVELOPMENT TOOL - REMOVE FOR PRODUCTION

// Basic security - disable in production environments
$environment = getenv('APP_ENV');
if ($environment === 'production') {
    die('This debugging tool is disabled in production environment');
}

// Display warning
echo '<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="warning">
        <h2>⚠️ Development Tool Only</h2>
        <p>This debugging tool should NOT be present on production servers. It is intended for development use only.</p>
    </div>
    <p><a href="../login.php">Return to login page</a></p>
</body>
</html>';

// Log access
error_log("Warning: Someone accessed the Google OAuth debug tool");