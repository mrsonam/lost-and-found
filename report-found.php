<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect to login if not authenticated
requireLogin();

$showSuccess = false; // flag to trigger popup
$field_errors = [];
$old = [
  'item-name' => '',
  'item-description' => '',
  'item-location' => '',
  'found-date' => '',
  'category' => '',
  'contact_method' => ''
];

$conn = getDBConnection();

if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Capture old values for repopulation
  $old['item-name'] = isset($_POST['item-name']) ? trim($_POST['item-name']) : '';
  $old['item-description'] = isset($_POST['item-description']) ? trim($_POST['item-description']) : '';
  $old['item-location'] = isset($_POST['item-location']) ? trim($_POST['item-location']) : '';
  $old['found-date'] = isset($_POST['found-date']) ? trim($_POST['found-date']) : '';
  $old['category'] = isset($_POST['category']) ? trim($_POST['category']) : '';
  $old['contact_method'] = isset($_POST['contact_method']) ? trim($_POST['contact_method']) : '';

  // Basic validations similar to report-lost
  if ($old['item-name'] === '') {
    $field_errors['item-name'] = 'Please enter the item name.';
  }
  if ($old['item-description'] === '') {
    $field_errors['item-description'] = 'Please provide a brief description.';
  }
  if ($old['item-location'] === '') {
    $field_errors['item-location'] = 'Please enter where you found the item.';
  }
  if ($old['found-date'] === '') {
    $field_errors['found-date'] = 'Please select the date found.';
  } else {
    $today = date('Y-m-d');
    if ($old['found-date'] > $today) {
      $field_errors['found-date'] = 'Date found cannot be in the future.';
    }
  }
  if ($old['category'] === '' || !ctype_digit($old['category'])) {
    $field_errors['category'] = 'Please select a category.';
  }
  $valid_methods = ['Email', 'Phone', 'Both'];
  if ($old['contact_method'] === '' || !in_array($old['contact_method'], $valid_methods)) {
    $field_errors['contact_method'] = 'Please choose a preferred contact method.';
  }

  // Prepare sanitized values for DB only after validation
  $title = mysqli_real_escape_string($conn, $old['item-name']);
  $description = mysqli_real_escape_string($conn, $old['item-description']);
  $location_found = mysqli_real_escape_string($conn, $old['item-location']);
  $date_found = $old['found-date'];
  $category_id = intval($old['category']);
  $contact_method = $old['contact_method'];
  $user_id = $_SESSION['user_id']; // user must be logged in now

  $image_path = '';
  $max_upload_bytes = 5 * 1024 * 1024; // 5 MB limit
  if (isset($_FILES['item-image'])) {
    $upload_error = $_FILES['item-image']['error'];
    if ($upload_error === UPLOAD_ERR_OK) {
      $upload_dir = 'uploads/items/';
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }

      if (!isset($_FILES['item-image']['size']) || $_FILES['item-image']['size'] <= 0) {
        $field_errors['item-image'] = 'We could not read the image size. Please try uploading again.';
      } else if ($_FILES['item-image']['size'] > $max_upload_bytes) {
        $field_errors['item-image'] = 'Image is too large. Please upload an image up to 5 MB.';
      } else {
        $file_extension = strtolower(pathinfo($_FILES['item-image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_extension, $allowed_extensions)) {
          $filename = uniqid() . '_' . time() . '.' . $file_extension;
          $upload_path = $upload_dir . $filename;

          if (move_uploaded_file($_FILES['item-image']['tmp_name'], $upload_path)) {
            $image_path = $upload_path;
          } else {
            $field_errors['item-image'] = 'We couldn\'t save your image. Please try again.';
          }
        } else {
          $field_errors['item-image'] = 'Unsupported image type. Use JPG, PNG, GIF, or WebP.';
        }
      }
    } else if ($upload_error !== UPLOAD_ERR_NO_FILE) {
      $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'Image is too large. Please upload an image up to 5 MB.',
        UPLOAD_ERR_FORM_SIZE => 'Image is too large. Please upload an image up to 5 MB.',
        UPLOAD_ERR_PARTIAL => 'Upload interrupted. Please try uploading the image again.',
        UPLOAD_ERR_NO_FILE => 'Please upload an image of the found item.',
        UPLOAD_ERR_NO_TMP_DIR => 'We\'re having trouble uploading right now. Please try again later.',
        UPLOAD_ERR_CANT_WRITE => 'We\'re having trouble saving your image. Please try again.',
        UPLOAD_ERR_EXTENSION => 'Upload was blocked by the server. Please try again.'
      ];
      $field_errors['item-image'] = isset($error_messages[$upload_error]) ? $error_messages[$upload_error] : 'We couldn\'t upload your image. Please try again.';
    } else {
      // No file provided; enforce custom message since the field is required
      $field_errors['item-image'] = 'Please upload an image of the found item.';
    }
  }

  if (empty($field_errors)) {
    $sql = "INSERT INTO items (user_id, category_id, title, description, item_type, location_found, date_found, contact_method, image_path) 
                VALUES (?, ?, ?, ?, 'found', ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssss", $user_id, $category_id, $title, $description, $location_found, $date_found, $contact_method, $image_path);

    if ($stmt->execute()) {
      $item_id = $stmt->insert_id;

      if (!empty($image_path)) {
        $sql_img = "INSERT INTO item_images (item_id, image_path, is_primary) VALUES (?, ?, TRUE)";
        $stmt_img = $conn->prepare($sql_img);
        $stmt_img->bind_param("is", $item_id, $image_path);
        $stmt_img->execute();
      }

      $showSuccess = true; // success flag
      // Clear old values after success
      $old = [
        'item-name' => '',
        'item-description' => '',
        'item-location' => '',
        'found-date' => '',
        'category' => '',
        'contact_method' => ''
      ];
    } else {
      $errorMsg = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
  }
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
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      font-weight: bold;
      z-index: 9999;
      animation: fadeInOut 3s forwards;
    }

    @keyframes fadeInOut {
      0% {
        opacity: 0;
        transform: translateY(-20px);
      }

      10% {
        opacity: 1;
        transform: translateY(0);
      }

      90% {
        opacity: 1;
      }

      100% {
        opacity: 0;
        transform: translateY(-20px);
      }
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
          <form action="report-found.php" method="post" enctype="multipart/form-data" class="floating-form" novalidate>

            <!-- Item Name -->
            <div class="form-group <?php echo isset($field_errors['item-name']) ? 'has-error' : ''; ?>">
              <input type="text" id="item-name" name="item-name" required placeholder=" " value="<?php echo htmlspecialchars($old['item-name']); ?>" class="<?php echo isset($field_errors['item-name']) ? 'field-error' : ''; ?>">
              <label for="item-name">Item Name</label>
              <?php if (isset($field_errors['item-name'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-name']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="form-group <?php echo isset($field_errors['item-description']) ? 'has-error' : ''; ?>">
              <textarea id="item-description" name="item-description" required placeholder=" " rows="3" class="<?php echo isset($field_errors['item-description']) ? 'field-error' : ''; ?>"><?php echo htmlspecialchars($old['item-description']); ?></textarea>
              <label for="item-description">Description</label>
              <?php if (isset($field_errors['item-description'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-description']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Location Found -->
            <div class="form-group <?php echo isset($field_errors['item-location']) ? 'has-error' : ''; ?>">
              <input type="text" id="item-location" name="item-location" required placeholder=" " value="<?php echo htmlspecialchars($old['item-location']); ?>" class="<?php echo isset($field_errors['item-location']) ? 'field-error' : ''; ?>">
              <label for="item-location">Location Found</label>
              <?php if (isset($field_errors['item-location'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-location']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Date Found -->
            <div class="form-group <?php echo isset($field_errors['found-date']) ? 'has-error' : ''; ?>">
              <input type="date" id="found-date" name="found-date" required placeholder=" " value="<?php echo htmlspecialchars($old['found-date']); ?>" class="<?php echo isset($field_errors['found-date']) ? 'field-error' : ''; ?>">
              <label for="found-date">Date Found</label>
              <?php if (isset($field_errors['found-date'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['found-date']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Select Category -->
            <div class="form-group <?php echo isset($field_errors['category']) ? 'has-error' : ''; ?>">
              <select name="category" required placeholder=" " class="<?php echo isset($field_errors['category']) ? 'field-error' : ''; ?>">
                <option value="">Select product's Category</option>
                <?php
                $conn = getDBConnection();
                $categories = $conn->query("SELECT id, name FROM categories");

                if ($categories && $categories->num_rows > 0) {
                  while ($row = $categories->fetch_assoc()) {
                    $selected = ($old['category'] !== '' && $old['category'] == (string)$row['id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                  }
                } else {
                  echo "<option value=''>No categories found</option>";
                }

                $conn->close();
                ?>
              </select>
              <label>Category</label>
              <?php if (isset($field_errors['category'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['category']); ?></span>
              <?php endif; ?>
            </div>

            <!-- Upload Image -->
            <div class="form-group <?php echo isset($field_errors['item-image']) ? 'has-error' : ''; ?>">
              <input type="file" id="item-image" name="item-image" accept="image/*" required class="<?php echo isset($field_errors['item-image']) ? 'field-error' : ''; ?>">
              <label for="item-image">Upload Image</label>
              <?php if (isset($field_errors['item-image'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item-image']); ?></span>
              <?php endif; ?>
              <div id="preview-container"></div>
            </div>

            <!-- Contact method -->
            <div class="form-group <?php echo isset($field_errors['contact_method']) ? 'has-error' : ''; ?>">
              <select name="contact_method" required placeholder=" " class="<?php echo isset($field_errors['contact_method']) ? 'field-error' : ''; ?>">
                <option value="">Select Contact Method</option>
                <option value="Email" <?php echo $old['contact_method'] === 'Email' ? 'selected' : ''; ?>>Email</option>
                <option value="Phone" <?php echo $old['contact_method'] === 'Phone' ? 'selected' : ''; ?>>Phone</option>
                <option value="Both" <?php echo $old['contact_method'] === 'Both' ? 'selected' : ''; ?>>Both</option>
              </select>
              <label>Preferred Contact Method</label>
              <?php if (isset($field_errors['contact_method'])): ?>
                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['contact_method']); ?></span>
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