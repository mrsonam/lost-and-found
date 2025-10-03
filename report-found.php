<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect to login if not authenticated
requireLogin();

$showSuccess = false; // flag to trigger popup

$conn = getDBConnection();

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['item-name']);
    $description = mysqli_real_escape_string($conn, $_POST['item-description']);
    $location_found = mysqli_real_escape_string($conn, $_POST['item-location']);
    $date_found = $_POST['found-date'];
    $category_id = intval($_POST['category']); 
    $contact_method = $_POST['contact_method'];
    $user_id = $_SESSION['user_id']; // user must be logged in now

    $image_path = null;
    if (!empty($_FILES['item-image']['name'])) {
        $upload_dir = "uploads/items/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES['item-image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['item-image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $sql = "INSERT INTO items (user_id, category_id, title, description, item_type, location_found, date_found, contact_method, image_path) 
            VALUES (?, ?, ?, ?, 'found', ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssss", $user_id, $category_id, $title, $description, $location_found, $date_found, $contact_method, $image_path);

    if ($stmt->execute()) {
        $item_id = $stmt->insert_id;

        if ($image_path) {
            $sql_img = "INSERT INTO item_images (item_id, image_path, is_primary) VALUES (?, ?, TRUE)";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->bind_param("is", $item_id, $image_path);
            $stmt_img->execute();
        }

        $showSuccess = true; // success flag
    } else {
        $errorMsg = "Error: " . $stmt->error;
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
      padding: 0 4px;
      color: #555;
    }
  </style>
</head>

<body>
  <?php include 'includes/navbar.php'; ?>

    <!-- Success Popup -->
    <div id="success-popup" class="popup-message">
    Item successfully reported!
    </div>

  <style>
    .popup-message {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        background: #16a34a;
        color: white;
        padding: 15px 20px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        font-weight: bold;
        z-index: 9999;
        animation: fadeInOut 3s forwards;
    }

    @keyframes fadeInOut {
      0% {opacity: 0; transform: translateY(-20px);}
      10% {opacity: 1; transform: translateY(0);}
      90% {opacity: 1;}
      100% {opacity: 0; transform: translateY(-20px);}
    }
</style>


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

            <!-- Select Category -->
            <div class="form-group">
                  <select name="category" required placeholder=" ">
                      <option value="">Select product's Category</option>
                      <?php
                      $conn = getDBConnection();
                      $categories = $conn->query("SELECT id, name FROM categories");

                      if ($categories && $categories->num_rows > 0) {
                          while ($row = $categories->fetch_assoc()) {
                              echo "<option value='{$row['id']}'>{$row['name']}</option>";
                          }
                      } else {
                          echo "<option value=''>No categories found</option>";
                      }

                      $conn->close();
                      ?>
                  </select>
                  <label>Category</label>
              </div>

            <!-- Upload Image -->
            <div class="form-group">
              <input type="file" id="item-image" name="item-image" accept="image/*" required>
              <label for="item-image">Upload Image</label>
              <div id="preview-container"></div>
            </div>

            <!-- Contact method -->
            <div class="form-group">
              <select name="contact_method" required placeholder=" ">
                <option value="">Select Contact Method</option>
                <option value="Email">Email</option>
                <option value="Phone">Phone</option>
                <option value="Both">Both</option>
              </select>
              <label>Preferred Contact Method</label>
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

  // Success popup
  document.addEventListener("DOMContentLoaded", function() {
    <?php if ($showSuccess): ?>
      let popup = document.getElementById("success-popup");
      popup.style.display = "block";
      setTimeout(() => {
          popup.style.display = "none";
      }, 3000);
    <?php endif; ?>
  });
</script>

</body>

</html>
