<?php
session_start();
require_once 'config/database.php';

$error_message = '';
$success_message = '';

// Process registration form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validate form data
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        error_log("Registration Failed - Missing required fields at " . date('Y-m-d H:i:s'));
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        error_log("Registration Failed - Password mismatch for email: " . $email . " at " . date('Y-m-d H:i:s'));
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        error_log("Registration Failed - Password too short for email: " . $email . " at " . date('Y-m-d H:i:s'));
        $error_message = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Registration Failed - Invalid email format: " . $email . " at " . date('Y-m-d H:i:s'));
        $error_message = 'Please enter a valid email address.';
    } else {
        $connection = getDBConnection();

        // Check if email already exists
        $existing_user = getSingleRow($connection, "SELECT id FROM users WHERE email = ?", "s", [$email]);

        if ($existing_user) {
            error_log("Registration Failed - Email already exists: " . $email . " at " . date('Y-m-d H:i:s'));
            $error_message = 'Email already exists.';
        } else {
            // Encrypt password for security
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Add new user to database
            $result = executeQuery($connection, "INSERT INTO users (email, password_hash, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)", "sssss", [$email, $password_hash, $first_name, $last_name, $phone]);

            if ($result) {
                error_log("Registration Success - New user registered: " . $email . " at " . date('Y-m-d H:i:s'));
                $success_message = 'Registration successful! You can now log in.';
                // Clear form data
                $email = $first_name = $last_name = $phone = '';
            } else {
                error_log("Registration Error - Database insert failed for email: " . $email . " at " . date('Y-m-d H:i:s'));
                error_log("Registration Error - Database error: " . mysqli_error($connection));
                $error_message = 'Registration failed. Please try again.';
            }
        }

        mysqli_close($connection);
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Lost & Found</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <div class="container auth-container">
            <div class="auth-form">
                <h1>Create Account</h1>
                <p>Join our community to report lost and found items</p>

                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full-width">Create Account</button>
                </form>

                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>