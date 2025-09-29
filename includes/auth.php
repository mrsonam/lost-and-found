<?php
// Helper functions for user authentication
require_once __DIR__ . '/../config/database.php';

// Check if a user is currently logged in
function isLoggedIn()
{
    // Check if user has both user_id and session_token in session
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

// Redirect to login page if user is not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        // Get current page URL to redirect back after login
        $current_page = $_SERVER['REQUEST_URI'];

        // Redirect to login page with current page as redirect parameter
        header('Location: login.php?redirect=' . urlencode($current_page));
        exit();
    }
}

// Get information about the currently logged-in user
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $connection = getDBConnection();
    $user = getSingleRow($connection, "SELECT * FROM users WHERE id = ?", "i", [$_SESSION['user_id']]);
    mysqli_close($connection);
    return $user;
}


// Log out the current user
function logout()
{
    if (isset($_SESSION['session_token'])) {
        // Delete session from database
        $connection = getDBConnection();
        executeQuery($connection, "DELETE FROM user_sessions WHERE session_token = ?", "s", [$_SESSION['session_token']]);
        mysqli_close($connection);
    }

    // Clear session data and start new session
    session_unset();
    session_destroy();
    session_start();
}

// Check if user's session is still valid
function validateSession()
{
    if (!isLoggedIn()) {
        return false;
    }

    // Check if session exists in database and hasn't expired
    $connection = getDBConnection();
    $result = getSingleRow($connection, "SELECT user_id FROM user_sessions WHERE session_token = ? AND expires_at > NOW()", "s", [$_SESSION['session_token']]);
    mysqli_close($connection);

    if (!$result) {
        logout();
        return false;
    }

    return true;
}

// Remove expired sessions from database
function cleanExpiredSessions()
{
    $connection = getDBConnection();
    executeQuery($connection, "DELETE FROM user_sessions WHERE expires_at < NOW()");
    mysqli_close($connection);
}
