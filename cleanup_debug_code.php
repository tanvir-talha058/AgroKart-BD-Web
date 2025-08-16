<?php
// Clean up debug statements in production files
// This script will modify the Google OAuth files to remove debug statements
// Run this script once to clean up your production code

echo "Starting code cleanup...\n";

// Files to clean up
$filesToClean = [
    'php/google_callback.php',
    'php/google_login.php',
];

// Patterns to remove
$patternsToRemove = [
    // Debug logging
    '/\s*error_log\([^;]*\);\s*\n/i',  // error_log statements
    '/\s*console\.log\([^;]*\);\s*\n/i',  // console.log statements
    '/\s*var_dump\([^;]*\);\s*\n/i',  // var_dump statements
    '/\s*print_r\([^;]*\);\s*\n/i',  // print_r statements

    // Debug tables and column check code in google_callback.php
    '/\s*\/\/ Add debug log to verify the table structure.*?\$tableCheck->num_rows.*?columns"\);\s*\n/s',
    '/\s*\/\/ Debug: Log the redirect URI.*?\n/s',
    '/\s*\/\/ Debug: Log token response.*?\n/s',

    // Remove table check code but keep the actual query
    '/\s*\/\/ First check which ID field exists.*?\n\s*\$idColumnName = \'id\';.*?error_log\("Using \'id\'.*?\n/s',
];

// Clean each file
foreach ($filesToClean as $file) {
    echo "Cleaning $file...\n";

    if (!file_exists($file)) {
        echo "  Error: File $file does not exist. Skipping.\n";
        continue;
    }

    // Read the file content
    $content = file_get_contents($file);
    if ($content === false) {
        echo "  Error: Could not read $file. Skipping.\n";
        continue;
    }

    // Make a backup
    $backupFile = $file . '.bak.' . date('YmdHis');
    if (!file_put_contents($backupFile, $content)) {
        echo "  Error: Could not create backup $backupFile. Skipping.\n";
        continue;
    }
    echo "  Created backup: $backupFile\n";

    // Apply patterns
    $originalSize = strlen($content);
    foreach ($patternsToRemove as $pattern) {
        $content = preg_replace($pattern, "\n", $content);
    }
    $newSize = strlen($content);

    // Write back
    if (file_put_contents($file, $content)) {
        $bytesSaved = $originalSize - $newSize;
        echo "  Done! Removed " . $bytesSaved . " bytes of debug code.\n";
    } else {
        echo "  Error: Could not write to $file.\n";
    }
}

echo "\nCreating a cleaner version of debug_google_oauth.php...\n";
$debugFile = 'php/debug_google_oauth.php';
$productionMode = false; // Set to true to remove this file completely

if ($productionMode) {
    // In production mode, remove the debug file completely
    if (file_exists($debugFile)) {
        if (rename($debugFile, 'unused_files_backup/php/' . basename($debugFile))) {
            echo "  Debug file moved to backup.\n";
        } else {
            echo "  Error: Could not move debug file to backup.\n";
        }
    }
} else {
    // In development mode, create a production-safe version with a warning
    $safeDebugContent = <<<'EOD'
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
EOD;

    file_put_contents('php/production_safe_debug.php', $safeDebugContent);
    echo "  Created production-safe debug file: production_safe_debug.php\n";
}

echo "\nCleanup complete!\n";
echo "Make sure to review the changes and test your website thoroughly.\n";
