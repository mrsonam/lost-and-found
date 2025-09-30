<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect to login if not authenticated
requireLogin();

$error_message = '';
$success_message = '';
$categories = [];

// Get current user info
$user = getCurrentUser();

// Get categories from database
$connection = getDBConnection();
$categories = getAllRows($connection, "SELECT id, name FROM categories ORDER BY name");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log form submission
    error_log("Form submitted - POST data: " . print_r($_POST, true));
    error_log("Form submitted - FILES data: " . print_r($_FILES, true));
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $location_lost = trim($_POST['location_lost'] ?? '');
    $date_lost = $_POST['date_lost'] ?? '';
    $contact_method = $_POST['contact_method'] ?? 'both';

    // Validate required fields
    if (empty($title) || empty($description) || empty($category_id) || empty($location_lost) || empty($date_lost)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/items/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $upload_path)) {
                    $image_path = $upload_path;
                } else {
                    $error_message = 'Failed to upload image. Please try again.';
                }
            } else {
                $error_message = 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images only.';
            }
        } else {
            // Debug upload errors
            if (isset($_FILES['item_image'])) {
                $upload_error = $_FILES['item_image']['error'];
                switch ($upload_error) {
                    case UPLOAD_ERR_INI_SIZE:
                        $error_message = 'File too large (server limit).';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_message = 'File too large (form limit).';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = 'File upload was interrupted.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // No file uploaded - this is normal for optional uploads
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error_message = 'No temporary directory available.';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error_message = 'Cannot write to disk.';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $error_message = 'Upload blocked by extension.';
                        break;
                    default:
                        $error_message = 'Unknown upload error: ' . $upload_error;
                        break;
                }
            }
        }

        // Insert item into database if no errors
        if (empty($error_message)) {
            $result = executeQuery(
                $connection,
                "INSERT INTO items (user_id, category_id, title, description, item_type, location_lost, date_lost, contact_method, image_path) VALUES (?, ?, ?, ?, 'lost', ?, ?, ?, ?)",
                "iissssss",
                [$user['id'], $category_id, $title, $description, $location_lost, $date_lost, $contact_method, $image_path]
            );

            if ($result) {
                $item_id = mysqli_insert_id($connection);

                // If image was uploaded, also insert into item_images table
                if (!empty($image_path)) {
                    executeQuery(
                        $connection,
                        "INSERT INTO item_images (item_id, image_path, is_primary) VALUES (?, ?, 1)",
                        "is",
                        [$item_id, $image_path]
                    );
                }

                error_log("Lost Item Reported - User ID: " . $user['id'] . " reported item: " . $title . " at " . date('Y-m-d H:i:s'));
                $success_message = 'Your lost item has been reported successfully!';

                // Clear form data
                $title = $description = $location_lost = $date_lost = '';
                $category_id = '';
                $contact_method = 'both';
            } else {
                error_log("Lost Item Report Failed - Database error for user ID: " . $user['id'] . " at " . date('Y-m-d H:i:s'));
                error_log("Lost Item Report Failed - Database error: " . mysqli_error($connection));
                $error_message = 'Failed to report lost item. Please try again.';
            }
        }
    }

    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lost Item - Lost & Found</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="lost-hero">
            <div class="container lost-hero-inner">
                <h1>Report Lost Item</h1>
                <p>Help us help you find your lost item by providing detailed information below.</p>
            </div>
        </section>

        <!-- Form Section -->
        <section class="lost-form-section">
            <div class="container">
                <div class="lost-form-card fade-in-up">
                    <h2>Lost Item Details</h2>

                    <?php if ($error_message): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <form action="" method="post" enctype="multipart/form-data" class="floating-form">
                        <!-- Item Title -->
                        <div class="form-group">
                            <label for="title">Item Title *</label>
                            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($title ?? ''); ?>">
                        </div>

                        <!-- Category -->
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category *</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" required rows="4"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>

                        <!-- Location Lost -->
                        <div class="form-group">
                            <label for="location_lost">Where did you lose it? *</label>
                            <input type="text" id="location_lost" name="location_lost" required value="<?php echo htmlspecialchars($location_lost ?? ''); ?>">
                        </div>

                        <!-- Date Lost -->
                        <div class="form-group">
                            <label for="date_lost">When did you lose it? *</label>
                            <input type="date" id="date_lost" name="date_lost" required value="<?php echo htmlspecialchars($date_lost ?? ''); ?>">
                        </div>

                        <!-- Contact Method -->
                        <div class="form-group">
                            <label for="contact_method">Preferred Contact Method</label>
                            <select id="contact_method" name="contact_method">
                                <option value="both" <?php echo (isset($contact_method) && $contact_method == 'both') ? 'selected' : ''; ?>>Email and Phone</option>
                                <option value="email" <?php echo (isset($contact_method) && $contact_method == 'email') ? 'selected' : ''; ?>>Email Only</option>
                                <option value="phone" <?php echo (isset($contact_method) && $contact_method == 'phone') ? 'selected' : ''; ?>>Phone Only</option>
                            </select>
                        </div>

                        <!-- Upload Image -->
                        <div class="form-group">
                            <label for="item_image">Upload Image (Optional)</label>
                            <input type="file" id="item_image" name="item_image" accept="image/*">
                            <div id="preview-container"></div>
                        </div>

                        <button type="submit" class="btn-animated">Report Lost Item</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Image preview functionality
        const input = document.getElementById('item_image');
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
                img.style.border = '2px solid #ddd';
                preview.appendChild(img);
            }
        });

        // Set max date to today for date input
        document.getElementById('date_lost').max = new Date().toISOString().split('T')[0];
    </script>
</body>

</html>