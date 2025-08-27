<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lost & Found - Homepage</title>
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <?php include 'components/navbar.php'; ?>
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
          <a href="#" class="btn btn-primary">Report Lost Item</a>
          <a href="#" class="btn btn-secondary">Report Found Item</a>
        </div>
      </div>
    </section>

    <!-- QUICK SEARCH -->
    <section class="search-quick">
      <div class="container">
        <div class="card search-quick-card">
          <h2 class="card-title">Quick Search</h2>
          <form class="search-form" role="search" aria-label="Quick search">
            <input type="search" placeholder="Search by keyword, brand, color..." aria-label="Keyword">
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
        <a href="#" class="view-all">View All →</a>
      </div>
      <div class="container">
        <div class="cards">
          <!-- Card 1 -->
          <article class="card item-card">
            <img src="images/phone.jpg" alt="iPhone 14 Pro" class="item-card-img">
            <div class="item-card-body">
              <div class="item-card-title">iPhone 14 Pro</div>
              <div class="item-card-meta">
                <span class="badge badge-lost">Lost</span>
              </div>
              <div class="item-card-date">2 days ago</div>
            </div>
          </article>

          <!-- Card 2 -->
          <article class="card item-card">
            <img src="images/wallet.jpg" alt="Brown Leather Wallet" class="item-card-img">
            <div class="item-card-body">
              <div class="item-card-title">Brown Leather Wallet</div>
              <div class="item-card-meta">
                <span class="badge badge-found">Found</span>
              </div>
              <div class="item-card-date">3 hours ago</div>
            </div>
          </article>

          <!-- Card 3 -->
          <article class="card item-card">
            <img src="images/key.jpg" alt="Silver Car Key" class="item-card-img">
            <div class="item-card-body">
              <div class="item-card-title">Car Key</div>
              <div class="item-card-meta">
                <span class="badge badge-lost">Lost</span>
              </div>
              <div class="item-card-date">1 day ago</div>
            </div>
          </article>

          <!-- Card 4 -->
          <article class="card item-card">
            <img src="images/bag.jpg" alt="Red Backpack" class="item-card-img">
            <div class="item-card-body">
              <div class="item-card-title">Red Backpack</div>
              <div class="item-card-meta">
                <span class="badge badge-found">Found</span>
              </div>
              <div class="item-card-date">7 days ago</div>
            </div>
          </article>
        </div>
      </div>
    </section>
  </main>
  <?php include 'components/footer.php'; ?>
</body>

</html>