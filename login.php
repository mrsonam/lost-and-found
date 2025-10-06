<?php
session_start();
require_once 'config/database.php';

$error_messages = [];
$field_errors = [];
$showSuccess = false;

// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Process login form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate email field
    if (empty($email)) {
        $field_errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    }

    // Validate password field
    if (empty($password)) {
        $field_errors['password'] = 'Password is required.';
    }

    // Only proceed with authentication if no field errors
    if (empty($field_errors)) {
        $connection = getDBConnection();

        // Find user by email in database
        $user = getSingleRow(
            $connection,
            "SELECT * FROM users WHERE email = ? AND otp_code IS NULL",
            "s",
            [$email]
        );

        // Check if password matches
        if ($user && password_verify($password, $user['password_hash'])) {
            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            // Create session token for security
            $session_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Save session to database
            $result = executeQuery($connection, "INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)", "iss", [$user['id'], $session_token, $expires_at]);

            if (!$result) {
                error_log("Login Error - Failed to insert session token for user ID: " . $user['id'] . " at " . date('Y-m-d H:i:s'));
                error_log("Login Error - Database error: " . mysqli_error($connection));
                $error_messages[] = 'Login failed. Please try again.';
            } else {
                $_SESSION['session_token'] = $session_token;
                error_log("Login Success - User ID: " . $user['id'] . " logged in successfully at " . date('Y-m-d H:i:s'));

                // Set success flag for toast display
                $showSuccess = true;
            }
        } else {
            if (!$user) {
                error_log("Login Failed - User not found or inactive for email: " . $email . " at " . date('Y-m-d H:i:s'));
                $field_errors['email'] = 'No account found with this email address.';
            } else {
                error_log("Login Failed - Invalid password for email: " . $email . " at " . date('Y-m-d H:i:s'));
                $field_errors['password'] = 'Incorrect password.';
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
    <title>Login - Lost & Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php if ($showSuccess): ?>
        <!-- Success Popup -->
        <div id="success-popup" class="popup-message">
            Login successful! Redirecting...
        </div>
    <?php endif; ?>

    <?php include 'includes/navbar.php'; ?>

    <main>
        <!-- Modern Hero Section -->
        <section class="auth-hero">
            <div class="auth-hero-background">
                <div class="auth-hero-pattern"></div>
            </div>
            <div class="container auth-hero-content">
                <div class="auth-hero-text">
                    <h1 class="auth-hero-title">Welcome Back</h1>
                    <p class="auth-hero-subtitle">Sign in to your account and continue helping reunite lost items with their owners</p>
                </div>
                <div class="auth-form-container">
                    <div class="auth-form-card">
                        <div class="auth-form-header">
                            <h2>Sign In</h2>
                            <p>Enter your credentials to access your account</p>
                        </div>

                        <?php if (!empty($error_messages)): ?>
                            <div class="error-messages">
                                <?php foreach ($error_messages as $error): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="floating-form" novalidate>
                            <div class="form-group <?php echo isset($field_errors['email']) ? 'has-error' : ''; ?>">
                                <input id="email" name="email" required placeholder=" "
                                    class="<?php echo isset($field_errors['email']) ? 'field-error' : ''; ?>"
                                    value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                <label for="email">Email Address</label>
                                <?php if (isset($field_errors['email'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['email']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group <?php echo isset($field_errors['password']) ? 'has-error' : ''; ?>">
                                <input type="password" id="password" name="password" required placeholder=" "
                                    class="<?php echo isset($field_errors['password']) ? 'field-error' : ''; ?>">
                                <label for="password">Password</label>
                                <?php if (isset($field_errors['password'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['password']); ?></span>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary btn-large">
                                <span>Sign In</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </form>

                        <div class="auth-links">
                            <p>Don't have an account? <a href="register.php">Create one here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Success popup and redirect
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($showSuccess): ?>
                // Show success toast
                let popup = document.getElementById("success-popup");
                if (popup) {
                    popup.style.display = "block";
                }

                // Redirect after 2 seconds
                setTimeout(() => {
                    const redirect = "<?php echo $_GET['redirect'] ?? 'index.php'; ?>";
                    window.location.href = redirect;
                }, 2000);
            <?php endif; ?>
        });
    </script>
</body>

</html>