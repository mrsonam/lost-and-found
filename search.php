<?php 
session_start();
include 'config.php'; 
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lost & Found - Homepage</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 2rem;
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .item {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }
    
    .item:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .item img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    
    .item-body {
      padding: 1.5rem;
    }
    
    .item-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #2c3e50;
    }
    
    .item-meta {
      color: #7f8c8d;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }
    
    .item-description {
      color: #34495e;
      font-size: 0.95rem;
      line-height: 1.4;
    }
    
    .available-search {
      display: flex;
      justify-content: center;
      padding: 3rem 2rem 2rem;
      background: white;
      position: relative;
    }
    
    .search-section {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      max-width: 700px;
      width: 100%;
    }
    
    .search-icon-container {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 60px;
      height: 60px;
      background: #14b8a6;
      border-radius: 50%;
      color: white;
      font-size: 1.8rem;
      box-shadow: 0 6px 20px rgba(20, 184, 166, 0.3);
      transition: all 0.3s ease;
      cursor: pointer;
      flex-shrink: 0;
    }
    
    .search-icon-container:hover {
      transform: scale(1.1) rotate(5deg);
      box-shadow: 0 8px 25px rgba(20, 184, 166, 0.4);
    }
    
    .search-container {
      position: relative;
      display: flex;
      flex: 1;
      background: #f8f9fa;
      border-radius: 12px;
      padding: 0.5rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .available-search input {
      padding: 1rem 1.5rem;
      border: 2px solid #e0e0e0;
      border-radius: 8px 0 0 8px;
      width: 100%;
      font-size: 1.1rem;
      transition: all 0.3s;
      background: white;
    }
    
    .available-search input:focus {
      outline: none;
      border-color: #14b8a6;
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }
    
    .available-search button {
      padding: 1rem 2rem;
      background: #14b8a6;
      color: white;
      border: none;
      border-radius: 0 8px 8px 0;
      cursor: pointer;
      font-size: 1.1rem;
      transition: all 0.3s;
      min-width: 120px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
    }
    
    .available-search button:hover {
      background: #0d9488;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(20, 184, 166, 0.4);
    }
    
    .clear-search {
      position: absolute;
      right: 130px;
      top: 50%;
      transform: translateY(-50%);
      background: #ddd;
      border: none;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      cursor: pointer;
      display: none;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: #666;
      transition: all 0.3s;
      z-index: 2;
    }
    
    .clear-search:hover {
      background: #ccc;
      transform: translateY(-50%) scale(1.1);
    }
    
    .no-items {
      text-align: center;
      padding: 3rem;
      font-size: 1.2rem;
      color: #7f8c8d;
    }
    
    .search-stats {
      text-align: center;
      padding: 1rem;
      color: #666;
      font-size: 0.9rem;
      background: #f8f9fa;
      margin: 0 2rem 2rem;
      border-radius: 8px;
    }
    
    .item-highlight {
      background-color: #fff9c4 !important;
      border: 2px solid #ffd54f !important;
    }
  
    
    .page-title {
      text-align: center;
      padding: 2rem 1rem 0;
      background: white;
    }
    
    .page-title h1 {
      font-size: 2.5rem;
      color: #2c3e50;
      margin-bottom: 0.5rem;
      font-weight: 700;
    }
    
    .page-title p {
      font-size: 1.1rem;
      color: #7f8c8d;
      margin-bottom: 2rem;
    }
    
    .item-status {
      display: inline-block;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .status-found { background: #d4edda; color: #155724; }
    .status-lost { background: #f8d7da; color: #721c24; }
    .status-other { background: #fff3cd; color: #856404; }
    .item-type { 
      margin-top: 0.5rem; 
      font-size: 0.85rem; 
      color: #95a5a6; 
      font-style: italic;
    }
    mark.item-highlight {
      background-color: #ffeb3b;
      padding: 0.1rem 0.2rem;
      border-radius: 2px;
    }
    
    @media (max-width: 768px) {
      .available-search {
        padding: 2rem 1rem 1rem;
      }
      
      .search-section {
        flex-direction: column;
        gap: 1rem;
      }
      
      .search-icon-container {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
      }
      
      .search-container {
        width: 100%;
        flex-direction: column;
        border-radius: 12px;
        padding: 0.8rem;
      }
      
      .available-search input {
        border-radius: 8px;
        margin-bottom: 0.5rem;
        padding: 1rem;
      }
      
      .available-search button {
        border-radius: 8px;
        width: 100%;
        padding: 1rem;
      }
      
      .clear-search {
        right: 15px;
        top: 35%;
      }
      
      .items-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
        gap: 1rem;
      }
      
      .search-stats {
        margin: 0 1rem 1rem;
      }
      
      .page-title h1 {
        font-size: 2rem;
      }
    }
  </style>
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
        
        while($row = $result->fetch_assoc()) {
            $image_path = !empty($row['image_path']) ? $row['image_path'] : 'images/placeholder.jpg';
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

    const style = document.createElement('style');
    style.textContent = `
        .item-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .status-found { background: #d4edda; color: #155724; }
        .status-lost { background: #f8d7da; color: #721c24; }
        .status-other { background: #fff3cd; color: #856404; }
        .item-type { 
            margin-top: 0.5rem; 
            font-size: 0.85rem; 
            color: #95a5a6; 
            font-style: italic;
        }
        mark.item-highlight {
            background-color: #ffeb3b;
            padding: 0.1rem 0.2rem;
            border-radius: 2px;
        }
    `;
    document.head.appendChild(style);

    initializeSearch();
});
</script>

  <?php include 'includes/footer.php'; ?>
</body>
</html>