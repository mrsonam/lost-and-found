<?php
// config.php - Database configuration file

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lost_and_found";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Database error: " . $e->getMessage());
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}
?>