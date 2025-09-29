<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Ensure database connection
$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect form data
    $title = mysqli_real_escape_string($conn, $_POST['item-name']);
    $description = mysqli_real_escape_string($conn, $_POST['item-description']);
    $location_found = mysqli_real_escape_string($conn, $_POST['item-location']);
    $date_found = $_POST['found-date'];
    $contact_name = mysqli_real_escape_string($conn, $_POST['contact-name']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact-email']);
    $contact_phone = !empty($_POST['contact-phone']) ? mysqli_real_escape_string($conn, $_POST['contact-phone']) : null;

    // For demo: assume logged-in user (replace with real session user_id if login exists)
    $user_id = $_SESSION['user_id'] ?? 1; 

    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['item-image']['name'])) {
        $upload_dir = "uploads/items/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES['item-image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['item-image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert into items table
    $sql = "INSERT INTO items (user_id, category_id, title, description, item_type, location_found, date_found, contact_method, image_path) 
            VALUES (?, NULL, ?, ?, 'found', ?, ?, 'both', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $user_id, $title, $description, $location_found, $date_found, $image_path);

    if ($stmt->execute()) {
        $item_id = $stmt->insert_id;

        // Save into item_images table
        if ($image_path) {
            $sql_img = "INSERT INTO item_images (item_id, image_path, is_primary) VALUES (?, ?, TRUE)";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->bind_param("is", $item_id, $image_path);
            $stmt_img->execute();
        }

        echo "<p style='color:green;'>Item successfully reported!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

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
      background: none !important;
      /* removes white background */
      padding: 0 4px;
      /* small padding so text doesn't touch borders */
      color: #555;
      /* keep label color readable */
    }
  </style>
</head>

<body>
  <?php include 'includes/navbar.php'; ?>

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
          <form action="report-found.php" method="post" enctype="multipart/form-data" class="floating-form">

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

  <?php include 'includes/footer.php'; ?>

  <script>
    // Image preview
    const input = document.getElementById('item-image');
    const preview = document.getElementById('preview-container');

    input.addEventListener('change', function() {
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