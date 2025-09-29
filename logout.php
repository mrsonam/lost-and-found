<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Logout the user
logout(); // if this already exists, keep it

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to home page
header('Location: index.php');
exit();
