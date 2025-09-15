<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Logout the user
logout();

// Redirect to home page
header('Location: index.php');
exit();

