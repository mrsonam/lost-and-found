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
  <div class="available-header">
    <h2>Available Items</h2>
    <p>Search from the list of found or reported items.</p>
  </div>

  <div class="available-search">
    <input type="text" placeholder="Search items by name or location..." />
    <button type="button">Search</button>
  </div>

  <div class="items-grid">
    <div class="item">
      <img src="images/wallet.jpg" alt="Brown Leather Wallet" style="object-fit: cover; height: 200px; width: 100%; border-radius: 8px 8px 0 0;" />
      <div class="item-body">
        <div class="item-title">Brown Leather Wallet</div>
        <div class="item-meta">Found at Main Street</div>
      </div>
    </div>

    <div class="item">
      <img src="images/smart watch.jpg" alt="Smart Watch" style="object-fit: cover; height: 200px; width: 100%; border-radius: 8px 8px 0 0;" />
      <div class="item-body">
        <div class="item-title">Smart Watch</div>
        <div class="item-meta">Item found by the lift</div>
      </div>
    </div>

    <div class="item">
      <img src="images/bag.jpg" alt="Red Backpack" style="object-fit: cover; height: 200px; width: 100%; border-radius: 8px 8px 0 0;" />
      <div class="item-body">
        <div class="item-title">Red Backpack</div>
        <div class="item-meta">Reported lost near Park Avenue</div>
      </div>
    </div>
  </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>

<script>
// Add hover animations and interactions
document.addEventListener('DOMContentLoaded', function() {
    // Item hover effects
    const items = document.querySelectorAll('.item');
    
    items.forEach(item => {
        // Mouse enter animation
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'all 0.3s ease';
            this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
        });
        
        // Mouse leave animation
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
        
        // Click animation
        item.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(-2px) scale(0.98)';
        });
        
        item.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(-5px) scale(1)';
        });
    });
    
    // Search button animation
    const searchBtn = document.querySelector('.available-search button');
    const searchInput = document.querySelector('.available-search input');
    
    if (searchBtn) {
        searchBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        searchBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        searchBtn.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        searchBtn.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1.05)';
        });
    }
    
    // Search input focus animation
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        searchInput.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
    }
});
</script>

</body>
</html>