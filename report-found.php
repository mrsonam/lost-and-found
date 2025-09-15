<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Report Found Item - Lost & Found</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/main.css">

  <style>
    /* Floating labels without background for Report Found page */
    .found-form-card .floating-form label {
      background: none !important; /* removes white background */
      padding: 0 4px; /* small padding so text doesn't touch borders */
      color: #555; /* keep label color readable */
    }
  </style>
</head>

<body>
  <?php include 'components/navbar.php'; ?>

  <main>
    <!-- Hero -->
    <section class="found-hero">
      <div class="container found-hero-inner">
        <h1>Report Found Item</h1>
        <p>Fill in the details below so we can help reunite items with their rightful owners.</p>
      </div>
    </section>

    <!-- Form Section -->
    <section class="found-form-section">
      <div class="container">
        <div class="found-form-card fade-in-up">
          <h2>Found Item Details</h2>
          <form action="#" method="post" enctype="multipart/form-data" class="floating-form">

            <!-- Item Name -->
            <div class="form-group">
              <input type="text" id="item-name" name="item-name" required placeholder=" ">
              <label for="item-name">Item Name</label>
            </div>

            <!-- Description -->
            <div class="form-group">
              <textarea id="item-description" name="item-description" required placeholder=" " rows="3"></textarea>
              <label for="item-description">Description</label>
            </div>

            <!-- Location Found -->
            <div class="form-group">
              <input type="text" id="item-location" name="item-location" required placeholder=" ">
              <label for="item-location">Location Found</label>
            </div>

            <!-- Date Found -->
            <div class="form-group">
              <input type="date" id="found-date" name="found-date" required placeholder=" ">
              <label for="found-date">Date Found</label>
            </div>

            <!-- Upload Image -->
            <div class="form-group">
              <input type="file" id="item-image" name="item-image" accept="image/*" required>
              <label for="item-image">Upload Image</label>
              <div id="preview-container"></div>
            </div>

            <!-- Your Name -->
            <div class="form-group">
              <input type="text" id="contact-name" name="contact-name" required placeholder=" ">
              <label for="contact-name">Your Name</label>
            </div>

            <!-- Email -->
            <div class="form-group">
              <input type="email" id="contact-email" name="contact-email" required placeholder=" ">
              <label for="contact-email">Email Address</label>
            </div>

            <!-- Phone Number (Optional) -->
            <div class="form-group">
              <input type="tel" id="contact-phone" name="contact-phone" placeholder=" ">
              <label for="contact-phone">Phone Number</label>
            </div>

            <button type="submit" class="btn-animated">Submit Item</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <?php include 'components/footer.php'; ?>

  <script>
    // Image preview
    const input = document.getElementById('item-image');
    const preview = document.getElementById('preview-container');

    input.addEventListener('change', function () {
      preview.innerHTML = '';
      const file = this.files[0];
      if (file) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.maxWidth = '200px';
        img.style.marginTop = '10px';
        img.style.borderRadius = '8px';
        preview.appendChild(img);
      }
    });
  </script>
</body>

</html>
