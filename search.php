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
  <title>Lost & Found - Search</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <?php include 'includes/navbar.php'; ?>

  <main>

    <!-- Search Form Section -->
    <section class="search-page-modern">
      <div class="container">
        <div class="search-card">
          <div class="search-header">
            <h2>Search Items</h2>
            <p>Enter keywords to find specific items in our database</p>
          </div>
          <form class="search-form-modern" id="searchForm">
            <div class="search-input-group">
              <input type="text" id="searchInput" placeholder="Search by name, location, or description..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
              <button type="button" id="clearSearch" class="btn btn-secondary" style="display: none;">Clear</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Results Section -->
    <section class="search-results-section">
      <div class="container">
        <div class="search-stats" id="searchStats">
          Loading items...
        </div>

        <div class="items-grid" id="itemsGrid">
          <?php
          // Query to get items along with their images and user information
          $sql = "SELECT i.*, ii.image_path, c.name as category_name, 
                CONCAT(u.first_name, ' ', u.last_name) as user_name, 
                u.email as user_email, u.phone as user_phone, i.contact_method
            FROM items i 
            LEFT JOIN item_images ii ON i.id = ii.item_id 
            LEFT JOIN categories c ON i.category_id = c.id 
            LEFT JOIN users u ON i.user_id = u.id
            ORDER BY i.created_at DESC";

          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            $totalItems = $result->num_rows;
            echo '<script>window.totalItems = ' . $totalItems . ';</script>';

            while ($row = $result->fetch_assoc()) {
              $image_path = !empty($row['image_path']) ? $row['image_path'] : 'images/placeholder.png';
              $item_title = htmlspecialchars($row['title'] ?? '');
              $item_location = htmlspecialchars($row['location_found'] ?? $row['location_lost']);
              $item_description = htmlspecialchars($row['description'] ?? '');
              $item_type = htmlspecialchars($row['item_type'] ?? '');
              $item_category = htmlspecialchars($row['category_name'] ?? '');
              $item_id = $row['id'];

              // User information
              $user_name = htmlspecialchars($row['user_name'] ?? 'Unknown User');
              $user_email = htmlspecialchars($row['user_email'] ?? '');
              $user_phone = htmlspecialchars($row['user_phone'] ?? '');
              $contact_method = htmlspecialchars($row['contact_method'] ?? '');

              $status_class = '';
              $status_text = '';
              if ($item_type == 'found') {
                $status_class = 'badge-found';
                $status_text = 'Found';
              } elseif ($item_type == 'lost') {
                $status_class = 'badge-lost';
                $status_text = 'Lost';
              } else {
                $status_class = 'badge-lost';
                $status_text = 'Lost';
              }

              echo '
            <div class="item" data-title="' . strtolower($item_title) . '" 
                 data-location="' . strtolower($item_location) . '" 
                 data-description="' . strtolower($item_description) . '"
                 data-category="' . strtolower($item_category) . '"
                 data-status="' . $item_type . '"
                 data-id="' . $item_id . '"
                 data-user-name="' . $user_name . '"
                 data-user-email="' . $user_email . '"
                 data-user-phone="' . $user_phone . '"
                 data-contact-method="' . $contact_method . '">
                <div class="item-image-container">
                    <img src="' . $image_path . '" alt="' . $item_title . '" />
                    <div class="item-badge">
                        <span class="badge ' . $status_class . '">' . $status_text . '</span>
                    </div>
                </div>
                <div class="item-content">
                    <div class="item-title">' . $item_title . '</div>
                    <div class="item-description">' . substr($item_description, 0, 100) . '...</div>
                    <div class="item-meta">
                        <span class="item-category">' . $item_category . '</span>
                        <span class="item-date">Posted by: ' . $user_name . '</span>
                    </div>
                </div>
            </div>';
            }
          } else {
            echo '<div class="no-items">
                <h3>No items found in the database</h3>
                <p>Please check your database connection or add some items to get started.</p>
              </div>';

            if (!$result) {
              echo '<div class="no-items error-message">
                    <h3>Database Error</h3>
                    <p>Error: ' . $conn->error . '</p>
                  </div>';
            }
          }
          ?>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal for item preview -->
  <div class="item-modal" id="itemModal">
    <div class="modal-content">
      <div class="modal-header">
        <button class="close-modal" id="closeModal">×</button>
      </div>
      <div class="modal-body">
        <div class="modal-image-container">
          <img id="modalItemImage" src="" alt="" class="modal-image">
          <div class="item-badge">
            <span class="badge" id="modalItemStatus"></span>
          </div>
        </div>
        <div class="modal-info">
          <h2 class="modal-title" id="modalItemTitle"></h2>
          <p class="modal-description" id="modalItemDescription"></p>
          <div class="info-grid">
            <div class="info-item">
              <div class="info-label">Location</div>
              <div class="info-value" id="modalItemLocation"></div>
            </div>
            <div class="info-item">
              <div class="info-label">Category</div>
              <div class="info-value" id="modalItemCategory"></div>
            </div>
          </div>
          <div class="contact-section">
            <h3>Contact Information</h3>
            <div class="contact-grid">
              <div class="contact-item">
                <div class="contact-label">Posted by</div>
                <div class="contact-value" id="modalItemUser"></div>
              </div>
              <div class="contact-item">
                <div class="contact-label">Email</div>
                <div class="contact-value" id="modalItemEmail"></div>
              </div>
              <div class="contact-item">
                <div class="contact-label">Phone</div>
                <div class="contact-value" id="modalItemPhone"></div>
              </div>
              <div class="contact-item">
                <div class="contact-label">Preferred Contact</div>
                <div class="contact-value" id="modalItemContactMethod"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const clearSearch = document.getElementById('clearSearch');
      const searchStats = document.getElementById('searchStats');
      const itemsGrid = document.getElementById('itemsGrid');
      const itemModal = document.getElementById('itemModal');
      const closeModal = document.getElementById('closeModal');
      const modalItemTitle = document.getElementById('modalItemTitle');
      const modalItemImage = document.getElementById('modalItemImage');
      const modalItemStatus = document.getElementById('modalItemStatus');
      const modalItemLocation = document.getElementById('modalItemLocation');
      const modalItemCategory = document.getElementById('modalItemCategory');
      const modalItemDescription = document.getElementById('modalItemDescription');
      const modalItemUser = document.getElementById('modalItemUser');
      const modalItemContactMethod = document.getElementById('modalItemContactMethod');
      const modalItemEmail = document.getElementById('modalItemEmail');
      const modalItemPhone = document.getElementById('modalItemPhone');

      let allItems = [];
      let searchTimeout;

      function initializeSearch() {
        allItems = Array.from(document.querySelectorAll('.item'));
        updateSearchStats(allItems.length, allItems.length);
        toggleClearButton();

        // Add click event listeners to all items
        allItems.forEach(item => {
          item.addEventListener('click', function() {
            showItemPreview(this);
          });
        });

        // Auto-search if there's a query parameter from homepage
        <?php if (!empty($_GET['q'])): ?>
          performRealTimeSearch();
        <?php endif; ?>
      }

      function showItemPreview(itemElement) {
        // Get item data from data attributes
        const title = itemElement.querySelector('.item-title').textContent;
        const imageSrc = itemElement.querySelector('img').src;
        const status = itemElement.querySelector('.item-badge .badge').textContent;
        const statusClass = itemElement.querySelector('.item-badge .badge').className;
        const location = itemElement.getAttribute('data-location');
        const category = itemElement.querySelector('.item-category').textContent;
        const description = itemElement.getAttribute('data-description');

        // Get user information from data attributes
        const userName = itemElement.getAttribute('data-user-name');
        const userEmail = itemElement.getAttribute('data-user-email');
        const userPhone = itemElement.getAttribute('data-user-phone');
        const contactMethod = itemElement.getAttribute('data-contact-method');

        // Populate modal with item data
        modalItemTitle.textContent = title;
        modalItemImage.src = imageSrc;
        modalItemImage.alt = title;
        modalItemStatus.textContent = status;
        modalItemStatus.className = 'badge ' + (status.toLowerCase() === 'found' ? 'badge-found' : 'badge-lost');
        modalItemLocation.textContent = location;
        modalItemCategory.textContent = category;
        modalItemDescription.textContent = description;
        modalItemUser.textContent = userName;
        modalItemContactMethod.textContent = contactMethod.charAt(0).toUpperCase() + contactMethod.slice(1);
        modalItemEmail.textContent = userEmail;
        modalItemPhone.textContent = userPhone;

        // Show the modal
        itemModal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
      }

      function closeItemPreview() {
        itemModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Re-enable scrolling
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

        const elements = item.querySelectorAll('.item-title, .item-description, .item-category');
        elements.forEach(element => {
          const text = element.textContent;
          const regex = new RegExp(`(${searchTerm})`, 'gi');
          const highlighted = text.replace(regex, '<mark class="item-highlight">$1</mark>');
          element.innerHTML = highlighted;
        });
      }

      function removeHighlights(item) {
        const elements = item.querySelectorAll('.item-title, .item-description, .item-category');
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
          clearSearch.style.display = 'inline-block';
        } else {
          clearSearch.style.display = 'none';
        }
      }

      function clearSearchHandler() {
        searchInput.value = '';
        searchInput.focus();
        performRealTimeSearch();
      }

      // Event listeners
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performRealTimeSearch, 300);
      });

      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          performRealTimeSearch();
        }
      });

      clearSearch.addEventListener('click', clearSearchHandler);


      // Modal event listeners
      closeModal.addEventListener('click', closeItemPreview);

      // Close modal when clicking outside the content
      itemModal.addEventListener('click', function(e) {
        if (e.target === itemModal) {
          closeItemPreview();
        }
      });

      // Close modal with Escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && itemModal.style.display === 'flex') {
          closeItemPreview();
        }
      });

      initializeSearch();
    });
  </script>

  <?php include 'includes/footer.php'; ?>
</body>

</html>