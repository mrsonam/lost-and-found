<?php
session_start();
require_once 'config/database.php';

$error_messages = [];
$field_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp'] ?? '');
    $email = $_SESSION['pending_email'] ?? '';

    // Validate OTP field
    if (empty($entered_otp)) {
        $field_errors['otp'] = 'OTP is required.';
    } elseif (!preg_match('/^\d{6}$/', $entered_otp)) {
        $field_errors['otp'] = 'OTP must be exactly 6 digits.';
    }

    if (!$email) {
        $error_messages[] = "Session expired. Please register again.";
    } else {
        $connection = getDBConnection();
        $user = getSingleRow($connection, "SELECT id, email, first_name, last_name, otp_code, otp_expires FROM users WHERE email = ?", "s", [$email]);

        if (!$user) {
            $error_messages[] = "User not found. Please register again.";
        } elseif (empty($user['otp_code'])) {
            $error_messages[] = "OTP already verified. Please log in.";
        } elseif (empty($field_errors) && $user['otp_code'] === $entered_otp && strtotime($user['otp_expires']) > time()) {
            // OTP correct → clear OTP and mark user verified
            executeQuery($connection, "UPDATE users SET otp_code = NULL, otp_expires = NULL WHERE email = ?", "s", [$email]);

            // Store user info in session (following login logic)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            // Create session token for security (following login logic)
            $session_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Save session to database
            $result = executeQuery($connection, "INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)", "iss", [$user['id'], $session_token, $expires_at]);

            if (!$result) {
                error_log("OTP Verification Error - Failed to insert session token for user ID: " . $user['id'] . " at " . date('Y-m-d H:i:s'));
                error_log("OTP Verification Error - Database error: " . mysqli_error($connection));
                $error_messages[] = 'Verification failed. Please try again.';
            } else {
                $_SESSION['session_token'] = $session_token;
                error_log("OTP Verification Success - User ID: " . $user['id'] . " verified and logged in successfully at " . date('Y-m-d H:i:s'));

                unset($_SESSION['pending_email']);
                header("Location: index.php");
                exit;
            }
        } else {
            if (empty($field_errors)) {
                error_log("OTP Verification Failed - Invalid or expired OTP for email: " . $email . " at " . date('Y-m-d H:i:s'));
                $field_errors['otp'] = "Invalid or expired OTP.";
            }
        }

        mysqli_close($connection);
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify OTP - Lost & Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <main>
        <!-- Modern Hero Section -->
        <section class="auth-hero">
            <div class="auth-hero-background">
                <div class="auth-hero-pattern"></div>
            </div>
            <div class="container auth-hero-content">
                <div class="auth-hero-text">
                    <h1 class="auth-hero-title">Verify Your Email</h1>
                    <p class="auth-hero-subtitle">Enter the OTP code sent to your email address to complete your registration</p>
                </div>
                <div class="auth-form-container">
                    <div class="auth-form-card">
                        <div class="auth-form-header">
                            <h2>Verify OTP</h2>
                            <p>Check your email for the verification code</p>
                        </div>

                        <?php if (!empty($error_messages)): ?>
                            <div class="error-messages">
                                <?php foreach ($error_messages as $error): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" novalidate class="floating-form">
                            <div class="form-group <?php echo isset($field_errors['otp']) ? 'has-error' : ''; ?>">
                                <input type="text" id="otp" name="otp" required placeholder=" "
                                    class="<?php echo isset($field_errors['otp']) ? 'field-error' : ''; ?>"
                                    value="<?php echo htmlspecialchars($entered_otp ?? ''); ?>">
                                <label for="otp">Enter OTP</label>
                                <?php if (isset($field_errors['otp'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['otp']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-large">
                                <span>Verify</span>
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
</body>

</html>