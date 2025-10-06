<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect to login if not authenticated
requireLogin();

$error_messages = [];
$field_errors = [];
$showSuccess = false;
$categories = [];
$old = [
    'title' => '',
    'description' => '',
    'category_id' => '',
    'location_lost' => '',
    'date_lost' => '',
    'contact_method' => 'both'
];

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
    // Capture old values for repopulation
    $old['title'] = isset($_POST['title']) ? trim($_POST['title']) : '';
    $old['description'] = isset($_POST['description']) ? trim($_POST['description']) : '';
    $old['category_id'] = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
    $old['location_lost'] = isset($_POST['location_lost']) ? trim($_POST['location_lost']) : '';
    $old['date_lost'] = isset($_POST['date_lost']) ? trim($_POST['date_lost']) : '';
    $old['contact_method'] = isset($_POST['contact_method']) ? trim($_POST['contact_method']) : 'both';

    // Basic validations similar to report-found
    if ($old['title'] === '') {
        $field_errors['title'] = 'Please enter the item title.';
    }
    if ($old['description'] === '') {
        $field_errors['description'] = 'Please provide a brief description.';
    }
    if ($old['category_id'] === '' || !ctype_digit($old['category_id'])) {
        $field_errors['category_id'] = 'Please select a category.';
    }
    if ($old['location_lost'] === '') {
        $field_errors['location_lost'] = 'Please enter where you lost the item.';
    }
    if ($old['date_lost'] === '') {
        $field_errors['date_lost'] = 'Please select the date lost.';
    } else {
        $today = date('Y-m-d');
        if ($old['date_lost'] > $today) {
            $field_errors['date_lost'] = 'Date lost cannot be in the future.';
        }
    }
    $valid_methods = ['email', 'phone', 'both'];
    if ($old['contact_method'] === '' || !in_array($old['contact_method'], $valid_methods)) {
        $field_errors['contact_method'] = 'Please choose a preferred contact method.';
    }

    // Prepare sanitized values for DB only after validation
    $title = mysqli_real_escape_string($connection, $old['title']);
    $description = mysqli_real_escape_string($connection, $old['description']);
    $location_lost = mysqli_real_escape_string($connection, $old['location_lost']);
    $date_lost = $old['date_lost'];
    $category_id = intval($old['category_id']);
    $contact_method = $old['contact_method'];

    // Only proceed if no field errors
    if (empty($field_errors)) {
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
                    $field_errors['item_image'] = 'Failed to upload image. Please try again.';
                }
            } else {
                $field_errors['item_image'] = 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images only.';
            }
        } else {
            // Debug upload errors
            if (isset($_FILES['item_image'])) {
                $upload_error = $_FILES['item_image']['error'];
                switch ($upload_error) {
                    case UPLOAD_ERR_INI_SIZE:
                        $field_errors['item_image'] = 'File too large (server limit).';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $field_errors['item_image'] = 'File too large (form limit).';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $field_errors['item_image'] = 'File upload was interrupted.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // No file uploaded - this is normal for optional uploads
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $field_errors['item_image'] = 'No temporary directory available.';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $field_errors['item_image'] = 'Cannot write to disk.';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $field_errors['item_image'] = 'Upload blocked by extension.';
                        break;
                    default:
                        $field_errors['item_image'] = 'Unknown upload error: ' . $upload_error;
                        break;
                }
            }
        }

        // Insert item into database if no errors
        if (empty($field_errors)) {
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
                $showSuccess = true;

                // Clear old values after success
                $old = [
                    'title' => '',
                    'description' => '',
                    'category_id' => '',
                    'location_lost' => '',
                    'date_lost' => '',
                    'contact_method' => 'both'
                ];
            } else {
                error_log("Lost Item Report Failed - Database error for user ID: " . $user['id'] . " at " . date('Y-m-d H:i:s'));
                error_log("Lost Item Report Failed - Database error: " . mysqli_error($connection));
                $error_messages[] = 'Failed to report lost item. Please try again.';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php if ($showSuccess): ?>
        <!-- Success Popup -->
        <div id="success-popup" class="popup-message">
            Lost item successfully reported!
        </div>
    <?php endif; ?>

    <?php include 'includes/navbar.php'; ?>

    <main>
        <!-- Modern Hero Section -->
        <section class="report-hero">
            <div class="report-hero-background">
                <div class="report-hero-pattern"></div>
            </div>
            <div class="container report-hero-content">
                <div class="report-hero-text">
                    <h1 class="report-hero-title">Report Lost Item</h1>
                    <p class="report-hero-subtitle">Help us help you find your lost item by providing detailed information below</p>
                </div>
            </div>
        </section>

        <!-- Form Section -->
        <section class="report-form-section">
            <div class="container">
                <div class="report-form-card">
                    <div class="report-form-header">
                        <h2>Lost Item Details</h2>
                        <p>Please provide as much detail as possible to help with identification</p>
                    </div>

                    <?php if (!empty($error_messages)): ?>
                        <div class="error-messages">
                            <?php foreach ($error_messages as $error): ?>
                                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>


                    <form action="" method="post" enctype="multipart/form-data" class="floating-form" novalidate>

                        <!-- Item Title -->
                        <div class="form-group <?php echo isset($field_errors['title']) ? 'has-error' : ''; ?>">
                            <input type="text" id="title" name="title" required placeholder=" " value="<?php echo htmlspecialchars($old['title']); ?>" class="<?php echo isset($field_errors['title']) ? 'field-error' : ''; ?>">
                            <label for="title">Item Title</label>
                            <?php if (isset($field_errors['title'])): ?>
                                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['title']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <div class="form-group <?php echo isset($field_errors['description']) ? 'has-error' : ''; ?>">
                            <textarea id="description" name="description" required placeholder=" " rows="3" class="<?php echo isset($field_errors['description']) ? 'field-error' : ''; ?>"><?php echo htmlspecialchars($old['description']); ?></textarea>
                            <label for="description">Description</label>
                            <?php if (isset($field_errors['description'])): ?>
                                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['description']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Location Lost -->
                        <div class="form-group <?php echo isset($field_errors['location_lost']) ? 'has-error' : ''; ?>">
                            <input type="text" id="location_lost" name="location_lost" required placeholder=" " value="<?php echo htmlspecialchars($old['location_lost']); ?>" class="<?php echo isset($field_errors['location_lost']) ? 'field-error' : ''; ?>">
                            <label for="location_lost">Location Lost</label>
                            <?php if (isset($field_errors['location_lost'])): ?>
                                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['location_lost']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Date Lost -->
                        <div class="form-group <?php echo isset($field_errors['date_lost']) ? 'has-error' : ''; ?>">
                            <input type="date" id="date_lost" name="date_lost" required placeholder=" " value="<?php echo htmlspecialchars($old['date_lost']); ?>" class="<?php echo isset($field_errors['date_lost']) ? 'field-error' : ''; ?>">
                            <label for="date_lost">Date Lost</label>
                            <?php if (isset($field_errors['date_lost'])): ?>
                                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['date_lost']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Select Category -->
                        <div class="form-group <?php echo isset($field_errors['category_id']) ? 'has-error' : ''; ?>">
                            <select name="category_id" required placeholder=" " class="<?php echo isset($field_errors['category_id']) ? 'field-error' : ''; ?>">
                                <option value="">Select product's Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($old['category_id'] !== '' && $old['category_id'] == (string)$category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label>Category</label>
                            <?php if (isset($field_errors['category_id'])): ?>
                                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['category_id']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Upload Image -->
                        <div class="form-group <?php echo isset($field_errors['item_image']) ? 'has-error' : ''; ?>">
                            <input type="file" id="item_image" name="item_image" accept="image/*" required class="<?php echo isset($field_errors['item_image']) ? 'field-error' : ''; ?>">
                            <label for="item_image">Upload Image</label>
                            <?php if (isset($field_errors['item_image'])): ?>
                                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['item_image']); ?></span>
                            <?php endif; ?>
                            <div id="preview-container"></div>
                        </div>

                        <!-- Contact method -->
                        <div class="form-group <?php echo isset($field_errors['contact_method']) ? 'has-error' : ''; ?>">
                            <select name="contact_method" required placeholder=" " class="<?php echo isset($field_errors['contact_method']) ? 'field-error' : ''; ?>">
                                <option value="">Select Contact Method</option>
                                <option value="both" <?php echo $old['contact_method'] === 'both' ? 'selected' : ''; ?>>Both</option>
                                <option value="email" <?php echo $old['contact_method'] === 'email' ? 'selected' : ''; ?>>Email</option>
                                <option value="phone" <?php echo $old['contact_method'] === 'phone' ? 'selected' : ''; ?>>Phone</option>
                            </select>
                            <label>Preferred Contact Method</label>
                            <?php if (isset($field_errors['contact_method'])): ?>
                                <span class="field-error-message"><?php echo htmlspecialchars($field_errors['contact_method']); ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large">
                            <span>Report Lost Item</span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </form>
                </div>
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

        // Success popup - auto-animate when present
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($showSuccess): ?>
                // Toast will auto-animate via CSS, no manual control needed
            <?php endif; ?>
        });
    </script>
</body>

</html>