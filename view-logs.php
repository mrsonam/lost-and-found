<?php
// Simple error log viewer for debugging authentication issues
// WARNING: This should only be used in development environments
// Remove this file in production for security reasons

session_start();

// Simple authentication check - you can remove this if you want
if (!isset($_SESSION['user_id'])) {
    echo "<h2>Please log in to view error logs</h2>";
    echo "<a href='login.php'>Login</a>";
    exit();
}

// Get the error log file path
$error_log_path = ini_get('error_log');
if (empty($error_log_path)) {
    $error_log_path = 'error.log'; // Default fallback
}

echo "<h2>Error Log Viewer</h2>";
echo "<p><strong>Log file:</strong> " . htmlspecialchars($error_log_path) . "</p>";
echo "<p><a href='?refresh=1'>Refresh</a> | <a href='?clear=1'>Clear Log</a></p>";

// Handle clear log request
if (isset($_GET['clear'])) {
    if (file_exists($error_log_path)) {
        file_put_contents($error_log_path, '');
        echo "<p style='color: green;'>Log cleared successfully!</p>";
    }
}

// Display recent log entries
if (file_exists($error_log_path)) {
    $log_content = file_get_contents($error_log_path);
    if (!empty($log_content)) {
        echo "<h3>Recent Log Entries:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 500px; overflow-y: auto;'>";
        echo htmlspecialchars($log_content);
        echo "</pre>";
    } else {
        echo "<p>No log entries found.</p>";
    }
} else {
    echo "<p>Error log file not found at: " . htmlspecialchars($error_log_path) . "</p>";
    echo "<p>Check your PHP configuration for the error_log setting.</p>";
}

// Show PHP error log configuration
echo "<h3>PHP Error Log Configuration:</h3>";
echo "<ul>";
echo "<li><strong>error_log:</strong> " . (ini_get('error_log') ?: 'Not set (using system default)') . "</li>";
echo "<li><strong>log_errors:</strong> " . (ini_get('log_errors') ? 'On' : 'Off') . "</li>";
echo "<li><strong>display_errors:</strong> " . (ini_get('display_errors') ? 'On' : 'Off') . "</li>";
echo "</ul>";
