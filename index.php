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
    LIMIT 8
");

mysqli_close($connection);
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
    <!-- HERO -->
    <section class="hero">
      <div class="container hero-inner">
        <h1>Lost Something? Found Something?</h1>
        <p class="sub">
          Connect lost items with their owners quickly and easily. Report what
          you’ve lost or found, and help reunite belongings with the right people.
        </p>
        <div class="hero-actions">
          <a href="report-lost.php" class="btn btn-primary">Report Lost Item</a>
          <a href="report-found.php" class="btn btn-secondary">Report Found Item</a>
        </div>
      </div>
    </section>

    <!-- QUICK SEARCH -->
    <section class="search-quick">
      <div class="container">
        <div class="card search-quick-card">
          <h2 class="card-title">Quick Search</h2>
          <form class="search-form" role="search" aria-label="Quick search" action="search.php" method="get">
            <input type="search" name="q" placeholder="Search by keyword, brand, color..." aria-label="Keyword" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
            <button class="btn btn-primary" type="submit">Search</button>
          </form>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-it-works">
      <div class="container">
        <h2 class="section-title">How It Works</h2>
        <p class="section-sub">Simple steps to reunite lost items with their owners</p>
        <div class="steps">
          <article class="step">
            <div class="step-icon">
              <img src="images/plus.png" alt="Plus">
            </div>
            <h3 class="step-title">1. Report an Item</h3>
            <p class="step-text">
              Lost something? Found something? Report it with details and pictures to help with identification.
            </p>
          </article>
          <article class="step">
            <div class="step-icon">
              <img src="images/search.png" alt="Search">
            </div>
            <h3 class="step-title">2. Search or Match</h3>
            <p class="step-text">
              Browse existing listings or get notified when potential matches are found in our database.
            </p>
          </article>
          <article class="step">
            <div class="step-icon">
              <img src="images/handshake.png" alt="Handshake">
            </div>
            <h3 class="step-title">3. Return to Owner</h3>
            <p class="step-text">
              Contact safely through our platform to arrange the return to the rightful owner.
            </p>
          </article>
        </div>
      </div>
    </section>

    <!-- RECENT ITEMS -->
    <section class="recent-items">
      <div class="container recent-items-header">
        <h2 class="section-title">Recent Items</h2>
        <a href="search.php" class="view-all">View All →</a>
      </div>
      <div class="container">
        <div class="cards">
          <?php if (!empty($recent_items)): ?>
            <?php foreach ($recent_items as $item): ?>
              <article class="card item-card">
                <?php
                // Determine image source
                $image_src = 'images/placeholder.jpg'; // Default placeholder
                if (!empty($item['image_path']) && file_exists($item['image_path'])) {
                  $image_src = $item['image_path'];
                } else {
                  // Use default images based on category or item type
                  switch (strtolower($item['category_name'])) {
                    case 'electronics':
                      $image_src = 'images/phone.jpg';
                      break;
                    case 'accessories':
                      $image_src = 'images/wallet.jpg';
                      break;
                    case 'keys':
                      $image_src = 'images/key.jpg';
                      break;
                    case 'clothing':
                      $image_src = 'images/bag.jpg';
                      break;
                    default:
                      $image_src = $item['item_type'] === 'lost' ? 'images/phone.jpg' : 'images/wallet.jpg';
                  }
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
                <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="item-card-img">
                <div class="item-card-body">
                  <div class="item-card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                  <div class="item-card-meta">
                    <span class="badge badge-<?php echo $item['item_type'] === 'lost' ? 'lost' : 'found'; ?>">
                      <?php echo ucfirst($item['item_type']); ?>
                    </span>
                    <?php if (!empty($item['category_name'])): ?>
                      <span class="category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="item-card-date"><?php echo $time_text; ?></div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-items">
              <p>No items found. Be the first to <a href="report-lost.php">report a lost item</a> or <a href="report-found.php">report a found item</a>!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>
  <?php include 'includes/footer.php'; ?>
</body>

</html>