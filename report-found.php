<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect to login if not authenticated
requireLogin();

// Ensure database connection
$conn = getDBConnection();
if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Collect form data
  $title = trim($_POST['item-name'] ?? '');
  $description = trim($_POST['item-description'] ?? '');
  $location_found = trim($_POST['item-location'] ?? '');
  $date_found = $_POST['found-date'] ?? '';
  $contact_name = trim($_POST['contact-name'] ?? '');
  $contact_email = trim($_POST['contact-email'] ?? '');
  $contact_phone = trim($_POST['contact-phone'] ?? '');

  // Field-specific validation
  $field_errors = [];
  $error_messages = [];

  if (empty($title)) {
    $field_errors['item-name'] = 'Item name is required.';
  }

  if (empty($description)) {
    $field_errors['item-description'] = 'Description is required.';
  }

  if (empty($location_found)) {
    $field_errors['item-location'] = 'Location where you found the item is required.';
  }

  if (empty($date_found)) {
    $field_errors['found-date'] = 'Date when you found the item is required.';
  } elseif ($date_found > date('Y-m-d')) {
    $field_errors['found-date'] = 'Date cannot be in the future.';
  }

  if (empty($contact_name)) {
    $field_errors['contact-name'] = 'Your name is required.';
  }

  if (empty($contact_email)) {
    $field_errors['contact-email'] = 'Email address is required.';
  } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
    $field_errors['contact-email'] = 'Please enter a valid email address.';
  }

  if (!empty($contact_phone) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $contact_phone)) {
    $field_errors['contact-phone'] = 'Please enter a valid phone number.';
  }

  // For demo: assume logged-in user (replace with real session user_id if login exists)
  $user_id = $_SESSION['user_id'] ?? 1;

  // Only proceed if no field errors
  if (empty($field_errors)) {

    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['item-image']['name'])) {
      $upload_dir = "uploads/items/";
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }

      $file_extension = strtolower(pathinfo($_FILES['item-image']['name'], PATHINFO_EXTENSION));
      $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

      if (in_array($file_extension, $allowed_extensions)) {
        $file_name = time() . "_" . basename($_FILES['item-image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['item-image']['tmp_name'], $target_file)) {
          $image_path = $target_file;
        } else {
          $field_errors['item-image'] = 'Failed to upload image. Please try again.';
        }
      } else {
        $field_errors['item-image'] = 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images only.';
      }
    }

    // Only proceed with database insertion if no field errors
    if (empty($field_errors)) {
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

        $success_message = "Item successfully reported!";
        // Clear form data
        $title = $description = $location_found = $date_found = $contact_name = $contact_email = $contact_phone = '';
      } else {
        $error_messages[] = "Error: " . $stmt->error;
      }

      $stmt->close();
    }
  }
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

          <?php if (!empty($error_messages)): ?>
            <div class="error-messages">
              <?php foreach ($error_messages as $error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
          <?php endif; ?>

          <form action="report-found.php" method="post" enctype="multipart/form-data" class="floating-form" novalidate>

            <!-- Item Name -->
            <div class="form-group <?php echo isset($field_errors['item-name']) ? 'has-error' : ''; ?>">
              <input type="text" id="item-name" name="item-name" placeholder=" "
                class="<?php echo isset($field_errors['item-name']) ? 'field-error' : ''; ?>"
                value="<?php echo htmlspecialchars($title ?? ''); ?>">
              <label for="item-name">Item Name</label>
              <?php if (isset($field_errors['item-name'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-name']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="form-group <?php echo isset($field_errors['item-description']) ? 'has-error' : ''; ?>">
              <textarea id="item-description" name="item-description" placeholder=" " rows="3"
                class="<?php echo isset($field_errors['item-description']) ? 'field-error' : ''; ?>"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
              <label for="item-description">Description</label>
              <?php if (isset($field_errors['item-description'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-description']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Location Found -->
            <div class="form-group <?php echo isset($field_errors['item-location']) ? 'has-error' : ''; ?>">
              <input type="text" id="item-location" name="item-location" placeholder=" "
                class="<?php echo isset($field_errors['item-location']) ? 'field-error' : ''; ?>"
                value="<?php echo htmlspecialchars($location_found ?? ''); ?>">
              <label for="item-location">Location Found</label>
              <?php if (isset($field_errors['item-location'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-location']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Date Found -->
            <div class="form-group <?php echo isset($field_errors['found-date']) ? 'has-error' : ''; ?>">
              <input type="date" id="found-date" name="found-date" placeholder=" "
                class="<?php echo isset($field_errors['found-date']) ? 'field-error' : ''; ?>"
                value="<?php echo htmlspecialchars($date_found ?? ''); ?>">
              <label for="found-date">Date Found</label>
              <?php if (isset($field_errors['found-date'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['found-date']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Upload Image -->
            <div class="form-group <?php echo isset($field_errors['item-image']) ? 'has-error' : ''; ?>">
              <input type="file" id="item-image" name="item-image" accept="image/*"
                class="<?php echo isset($field_errors['item-image']) ? 'field-error' : ''; ?>">
              <label for="item-image">Upload Image</label>
              <?php if (isset($field_errors['item-image'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-image']); ?></span>
              <?php endif; ?>
              <div id="preview-container"></div>
            </div>

            <!-- Your Name -->
            <div class="form-group <?php echo isset($field_errors['contact-name']) ? 'has-error' : ''; ?>">
              <input type="text" id="contact-name" name="contact-name" placeholder=" "
                class="<?php echo isset($field_errors['contact-name']) ? 'field-error' : ''; ?>"
                value="<?php echo htmlspecialchars($contact_name ?? ''); ?>">
              <label for="contact-name">Your Name</label>
              <?php if (isset($field_errors['contact-name'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['contact-name']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group <?php echo isset($field_errors['contact-email']) ? 'has-error' : ''; ?>">
              <input type="email" id="contact-email" name="contact-email" placeholder=" "
                class="<?php echo isset($field_errors['contact-email']) ? 'field-error' : ''; ?>"
                value="<?php echo htmlspecialchars($contact_email ?? ''); ?>">
              <label for="contact-email">Email Address</label>
              <?php if (isset($field_errors['contact-email'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['contact-email']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Phone Number (Optional) -->
            <div class="form-group <?php echo isset($field_errors['contact-phone']) ? 'has-error' : ''; ?>">
              <input type="tel" id="contact-phone" name="contact-phone" placeholder=" "
                class="<?php echo isset($field_errors['contact-phone']) ? 'field-error' : ''; ?>"
                value="<?php echo htmlspecialchars($contact_phone ?? ''); ?>">
              <label for="contact-phone">Phone Number</label>
              <?php if (isset($field_errors['contact-phone'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['contact-phone']); ?></span>
              <?php endif; ?>
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