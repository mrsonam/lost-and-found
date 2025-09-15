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
      <img src="https://via.placeholder.com/300x180" alt="Item 1" />
      <div class="item-body">
        <div class="item-title">Black Wallet</div>
        <div class="item-meta">Found at Main Street</div>
      </div>
    </div>

    <div class="item">
      <img src="https://via.placeholder.com/300x180" alt="Item 2" />
      <div class="item-body">
        <div class="item-title">Silver Bracelet</div>
        <div class="item-meta">Reported lost near Park Avenue</div>
      </div>
    </div>

    <div class="item">
      <img src="https://via.placeholder.com/300x180" alt="Item 3" />
      <div class="item-body">
        <div class="item-title">Set of Keys</div>
        <div class="item-meta">Found outside Library</div>
      </div>
    </div>
</body>

</main>
  <?php include 'components/footer.php'; ?>
</body>

</html>
