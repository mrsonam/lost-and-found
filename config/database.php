<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lost_and_found');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create database connection using MySQLi
function getDBConnection()
{
    // Create MySQLi connection
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check if connection failed
    if (!$connection) {
        error_log("Database connection failed: " . mysqli_connect_error());
        die("Database connection failed. Please try again later.");
    }

    // Set character set to utf8mb4
    mysqli_set_charset($connection, DB_CHARSET);

    return $connection;
}

// Helper function to execute prepared statements with MySQLi
function executeQuery($connection, $query, $types = "", $params = [])
{
    $stmt = mysqli_prepare($connection, $query);

    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($connection));
        return false;
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

// Helper function to get single row
function getSingleRow($connection, $query, $types = "", $params = [])
{
    $result = executeQuery($connection, $query, $types, $params);
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

// Helper function to get all rows
function getAllRows($connection, $query, $types = "", $params = [])
{
    $result = executeQuery($connection, $query, $types, $params);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return false;
}
