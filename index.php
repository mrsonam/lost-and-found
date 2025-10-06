<?php
require_once 'config/database.php';

// Get database connection
$connection = getDBConnection();

// Fetch recent items from database
$recent_items = getAllRows($connection, "
    SELECT 
        i.id,
        i.title,
        i.description,
        i.item_type,
        i.location_lost,
        i.location_found,
        i.date_lost,
        i.date_found,
        i.created_at,
        i.image_path,
        c.name as category_name,
        u.first_name,
        u.last_name
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN users u ON i.user_id = u.id
    WHERE i.status = 'active'
    ORDER BY i.created_at DESC
    LIMIT 3
");

mysqli_close($connection);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lost & Found - Homepage</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <?php include 'includes/navbar.php'; ?>
  <main>
    <!-- HERO -->
    <section class="hero-modern">
      <div class="hero-background">
        <div class="hero-pattern"></div>
      </div>
      <div class="container hero-content">
        <div class="hero-text">
          <h1 class="hero-title">Reunite Lost Items with Their Owners</h1>
          <p class="hero-subtitle">
            The most trusted platform for reporting and finding lost belongings.
            Join thousands of people who have successfully reunited with their items.
          </p>
          <div class="hero-actions">
            <a href="report-lost.php" class="btn btn-primary btn-large">
              <span>Report Lost Item</span>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            <a href="report-found.php" class="btn btn-secondary btn-large">
              <span>Report Found Item</span>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
          </div>
        </div>
        <div class="hero-visual">
          <div class="hero-card">
            <div class="card-header">
              <div class="card-avatar"></div>
              <div class="card-info">
                <h4>Sarah Johnson</h4>
                <p>Found: iPhone 13 Pro</p>
              </div>
            </div>
            <div class="card-content">
              <div class="item-preview">
                <img class="item-image" src="images/phone.jpg" alt="iPhone 13 Pro">
                <div class="item-details">
                  <h5>iPhone 13 Pro</h5>
                  <p>Found at Central Park</p>
                  <span class="status-badge found">Found</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- QUICK SEARCH -->
    <section class="search-quick-modern">
      <div class="container">
        <div class="search-card">
          <div class="search-header">
            <h2>Find Your Lost Item</h2>
            <p>Search through thousands of reported items to find what you're looking for</p>
          </div>
          <form class="search-form-modern" role="search" aria-label="Quick search" action="search.php" method="get">
            <div class="search-input-group">
              <input type="search" name="q" placeholder="Search by keyword, brand, color, location..." aria-label="Keyword" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
              <button class="btn btn-primary" type="submit">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <path d="M21 21L16.514 16.506L21 21ZM19 10.5C19 15.194 15.194 19 10.5 19C5.806 19 2 15.194 2 10.5C2 5.806 5.806 2 10.5 2C15.194 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Search
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features-section">
      <div class="container">
        <div class="features-header">
          <h2 class="section-title">How It Works</h2>
          <p class="section-subtitle">Simple steps to reunite lost items with their owners</p>
        </div>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <h3>1. Report an Item</h3>
            <p>Lost something? Found something? Report it with details and pictures to help with identification.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M21 21L16.514 16.506L21 21ZM19 10.5C19 15.194 15.194 19 10.5 19C5.806 19 2 15.194 2 10.5C2 5.806 5.806 2 10.5 2C15.194 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <h3>2. Search or Match</h3>
            <p>Browse existing listings or get notified when potential matches are found in our database.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M8 12H16M12 8V16M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <h3>3. Return to Owner</h3>
            <p>Contact safely through our platform to arrange the return to the rightful owner.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- RECENT ITEMS -->
    <section class="recent-items-modern">
      <div class="container">
        <div class="recent-items-header">
          <div class="header-content">
            <h2 class="section-title">Recently Reported Items</h2>
            <p class="section-subtitle">Latest items reported by our community members</p>
          </div>
          <a href="search.php" class="btn btn-outline">View All Items</a>
        </div>
        <div class="items-grid">
          <?php if (!empty($recent_items)): ?>
            <?php foreach ($recent_items as $item): ?>
              <article class="item-card-modern">
                <?php
                // Determine image source
                $image_src = 'images/placeholder.png'; // Default placeholder
                if (!empty($item['image_path']) && file_exists($item['image_path'])) {
                  $image_src = $item['image_path'];
                }

                // Format date
                $date_created = new DateTime($item['created_at']);
                $time_ago = $date_created->diff(new DateTime());
                $time_text = '';
                if ($time_ago->days > 0) {
                  $time_text = $time_ago->days . ' day' . ($time_ago->days > 1 ? 's' : '') . ' ago';
                } elseif ($time_ago->h > 0) {
                  $time_text = $time_ago->h . ' hour' . ($time_ago->h > 1 ? 's' : '') . ' ago';
                } else {
                  $time_text = 'Just now';
                }
                ?>
                <div class="item-image-container">
                  <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="item-image">
                  <div class="item-badge">
                    <span class="badge badge-<?php echo $item['item_type'] === 'lost' ? 'lost' : 'found'; ?>">
                      <?php echo ucfirst($item['item_type']); ?>
                    </span>
                  </div>
                </div>
                <div class="item-content">
                  <h3 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                  <p class="item-description"><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></p>
                  <div class="item-meta">
                    <?php if (!empty($item['category_name'])): ?>
                      <span class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                    <?php endif; ?>
                    <span class="item-date"><?php echo $time_text; ?></span>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-items-modern">
              <div class="no-items-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                  <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
              <h3>No Items Yet</h3>
              <p>Be the first to report a lost or found item in your community!</p>
              <div class="no-items-actions">
                <a href="report-lost.php" class="btn btn-primary">Report Lost Item</a>
                <a href="report-found.php" class="btn btn-secondary">Report Found Item</a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

  </main>
  <?php include 'includes/footer.php'; ?>
</body>

</html>