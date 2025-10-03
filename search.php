<?php
session_start();
include 'config.php';
require_once 'includes/auth.php';
// Redirect to login if not authenticated   
requireLogin();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lost & Found - Homepage</title>
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <?php include 'includes/navbar.php'; ?>

  <main>
    <section class="available-items-page">
      <div class="page-title">
        <h1>Available Items</h1>
        <p>Search from the list of found or reported items.</p>
      </div>

      <div class="available-search">
        <div class="search-section">
          <div class="search-icon-container" title="Click to focus search" id="searchIcon">
            <span style="filter: brightness(0) invert(1);">🔍</span>
          </div>
          <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search items by name, location, or description..." />
            <button type="button" id="clearSearch" class="clear-search" title="Clear search">×</button>
            <button type="button" id="searchBtn">Search</button>
          </div>
        </div>
      </div>

      <div class="search-stats" id="searchStats">
        Loading items...
      </div>

      <div class="items-grid" id="itemsGrid">
        <?php
        // Query to get items along with their images
        $sql = "SELECT i.*, ii.image_path, c.name as category_name 
            FROM items i 
            LEFT JOIN item_images ii ON i.id = ii.item_id 
            LEFT JOIN categories c ON i.category_id = c.id 
            ORDER BY i.created_at DESC";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
          $totalItems = $result->num_rows;
          echo '<script>window.totalItems = ' . $totalItems . ';</script>';

          while ($row = $result->fetch_assoc()) {
            $image_path = !empty($row['image_path']) ? $row['image_path'] : 'images/placeholder.png';
            $item_title = htmlspecialchars($row['title'] ?? '');
            $item_location = htmlspecialchars($row['location_found'] ?? '');
            $item_description = htmlspecialchars($row['description'] ?? '');
            $item_status = htmlspecialchars($row['status'] ?? '');
            $item_type = htmlspecialchars($row['item_type'] ?? '');
            $item_category = htmlspecialchars($row['category_name'] ?? '');

            $status_class = '';
            if ($item_status == 'found') {
              $status_class = 'status-found';
            } elseif ($item_status == 'lost') {
              $status_class = 'status-lost';
            } else {
              $status_class = 'status-other';
            }

            echo '
            <div class="item" data-title="' . strtolower($item_title) . '" 
                 data-location="' . strtolower($item_location) . '" 
                 data-type="' . $item_type . '"
                 data-description="' . strtolower($item_description) . '"
                 data-category="' . strtolower($item_category) . '"
                 data-status="' . $item_status . '">
                <img src="' . $image_path . '" alt="' . $item_title . '" />
                <div class="item-body">
                    <div class="item-status ' . $status_class . '">' . ucfirst($item_status) . '</div>
                    <div class="item-title">' . $item_title . '</div>
                    <div class="item-meta">Location: ' . $item_location . '</div>
                    <div class="item-description">' . substr($item_description, 0, 100) . '...</div>
                    <div class="item-type">Category: ' . $item_category . '</div>
                </div>
            </div>';
          }
        } else {
          echo '<div class="no-items">
                <h3>No items found in the database</h3>
                <p>Please check your database connection or add some items to get started.</p>
              </div>';

          if (!$result) {
            echo '<div class="no-items" style="color: #e74c3c;">
                    <h3>Database Error</h3>
                    <p>Error: ' . $conn->error . '</p>
                  </div>';
          }
        }
        ?>
      </div>
    </section>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const searchBtn = document.getElementById('searchBtn');
      const clearSearch = document.getElementById('clearSearch');
      const searchStats = document.getElementById('searchStats');
      const itemsGrid = document.getElementById('itemsGrid');
      const searchIcon = document.getElementById('searchIcon');
      let allItems = [];
      let searchTimeout;

      function initializeSearch() {
        allItems = Array.from(document.querySelectorAll('.item'));
        updateSearchStats(allItems.length, allItems.length);
        toggleClearButton();
      }

      function performRealTimeSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleItems = 0;

        allItems.forEach(item => {
          const title = item.getAttribute('data-title') || '';
          const location = item.getAttribute('data-location') || '';
          const description = item.getAttribute('data-description') || '';
          const category = item.getAttribute('data-category') || '';
          const type = item.getAttribute('data-type') || '';
          const status = item.getAttribute('data-status') || '';

          const matches = searchTerm === '' ||
            title.includes(searchTerm) ||
            location.includes(searchTerm) ||
            description.includes(searchTerm) ||
            category.includes(searchTerm) ||
            type.includes(searchTerm) ||
            status.includes(searchTerm);

          if (matches) {
            item.style.display = 'block';
            visibleItems++;
            highlightText(item, searchTerm);
          } else {
            item.style.display = 'none';
            removeHighlights(item);
          }
        });

        updateSearchStats(visibleItems, allItems.length);
        toggleClearButton();
      }

      function highlightText(item, searchTerm) {
        if (!searchTerm) {
          removeHighlights(item);
          return;
        }

        const elements = item.querySelectorAll('.item-title, .item-meta, .item-description');
        elements.forEach(element => {
          const text = element.textContent;
          const regex = new RegExp(`(${searchTerm})`, 'gi');
          const highlighted = text.replace(regex, '<mark class="item-highlight">$1</mark>');
          element.innerHTML = highlighted;
        });
      }

      function removeHighlights(item) {
        const elements = item.querySelectorAll('.item-title, .item-meta, .item-description');
        elements.forEach(element => {
          element.innerHTML = element.textContent;
        });
      }

      function updateSearchStats(visible, total) {
        if (visible === total) {
          searchStats.textContent = `Showing all ${total} items`;
          searchStats.style.color = '#27ae60';
        } else if (visible === 0) {
          searchStats.textContent = `No items found matching your search`;
          searchStats.style.color = '#e74c3c';
        } else {
          searchStats.textContent = `Showing ${visible} of ${total} items`;
          searchStats.style.color = '#3498db';
        }
      }

      function toggleClearButton() {
        if (searchInput.value.trim() !== '') {
          clearSearch.style.display = 'flex';
        } else {
          clearSearch.style.display = 'none';
        }
      }

      function clearSearchHandler() {
        searchInput.value = '';
        searchInput.focus();
        performRealTimeSearch();
      }

      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performRealTimeSearch, 300);
      });

      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          performRealTimeSearch();
        }
      });

      searchBtn.addEventListener('click', performRealTimeSearch);
      clearSearch.addEventListener('click', clearSearchHandler);

      searchIcon.addEventListener('click', function() {
        searchInput.focus();
        searchIcon.style.transform = 'scale(1.1) rotate(10deg)';
        setTimeout(() => {
          searchIcon.style.transform = 'scale(1.1)';
        }, 200);
      });

      searchInput.addEventListener('focus', function() {
        this.style.transform = 'scale(1.02)';
        this.style.borderColor = '#14b8a6';
      });

      searchInput.addEventListener('blur', function() {
        this.style.transform = 'scale(1)';
        this.style.borderColor = '#e0e0e0';
      });

      allItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-5px)';
          this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
        });

        item.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
          this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        });
      });


      initializeSearch();
    });
  </script>

  <?php include 'includes/footer.php'; ?>
</body>

</html>