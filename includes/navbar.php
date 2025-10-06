<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once 'includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']); // get the current page
$user = getCurrentUser();

echo '
  <header class="site-header">
    <div class="container nav">
      <a class="brand" href="index.php">Lost & Found</a>
      
      <!-- Hamburger Menu Button -->
      <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
      </button>
      
      <!-- Navigation Menu -->
      <nav class="nav-links" id="nav-links" aria-label="Primary">
        <a class="' . ($current_page == 'index.php' ? 'active' : '') . '" href="index.php">Home</a>';

// Show report links only when logged in
if ($user) {
  echo '
        <a class="' . ($current_page == 'report-lost.php' ? 'active' : '') . '" href="report-lost.php">Report Lost</a>
        <a class="' . ($current_page == 'report-found.php' ? 'active' : '') . '" href="report-found.php">Report Found</a>
        <a class="' . ($current_page == 'search.php' ? 'active' : '') . '" href="search.php">Search</a>';
}

echo '
        <a class="' . ($current_page == 'contact.php' ? 'active' : '') . '" href="contact.php">Contact</a>';

// Show login/register as regular nav links when not logged in
if (!$user) {
  echo '
        <a class="' . ($current_page == 'login.php' ? 'active' : '') . '" href="login.php">Login</a>
        <a class="' . ($current_page == 'register.php' ? 'active' : '') . '" href="register.php">Register</a>';
} else {
  echo '
        <a href="logout.php" class="btn btn-outline">Logout</a>';
}

echo '
      </nav>
    </div>
  </header>';
