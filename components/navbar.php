<?php
$current_page = basename($_SERVER['PHP_SELF']); // get the current page
echo '
  <header class="site-header">
    <div class="container nav">
      <a class="brand" href="index.php">Lost & Found</a>
      <nav class="nav-links" aria-label="Primary">
        <a class="' . ($current_page == 'index.php' ? 'active' : '') . '" href="index.php">Home</a> <!-- active if the current page is index.php -->
        <a class="' . ($current_page == 'report-lost.php' ? 'active' : '') . '" href="report-lost.php">Report Lost</a> <!-- active if the current page is report-lost.php -->
        <a class="' . ($current_page == 'report-found.php' ? 'active' : '') . '" href="report-found.php">Report Found</a> <!-- active if the current page is report-found.php -->
        <a class="' . ($current_page == 'search.php' ? 'active' : '') . '" href="search.php">Search</a> <!-- active if the current page is search.php -->
        <a class="' . ($current_page == 'contact.php' ? 'active' : '') . '" href="contact.php">Contact</a> <!-- active if the current page is contact.php -->
      </nav>
    </div>
  </header>';
