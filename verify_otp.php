<?php
session_start();
require_once 'config/database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);
    $email = $_SESSION['pending_email'] ?? '';

    if (!$email) {
        $error_message = "Session expired. Please register again.";
    } else {
        $connection = getDBConnection();
        $user = getSingleRow($connection, "SELECT id, email, first_name, last_name, otp_code, otp_expires FROM users WHERE email = ?", "s", [$email]);

        if (!$user) {
            $error_message = "User not found. Please register again.";
        } elseif (empty($user['otp_code'])) {
            $error_message = "OTP already verified. Please log in.";
        } elseif ($user['otp_code'] === $entered_otp && strtotime($user['otp_expires']) > time()) {
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
                $error_message = 'Verification failed. Please try again.';
            } else {
                $_SESSION['session_token'] = $session_token;
                error_log("OTP Verification Success - User ID: " . $user['id'] . " verified and logged in successfully at " . date('Y-m-d H:i:s'));

                unset($_SESSION['pending_email']);
                header("Location: index.php");
                exit;
            }
        } else {
            error_log("OTP Verification Failed - Invalid or expired OTP for email: " . $email . " at " . date('Y-m-d H:i:s'));
            $error_message = "Invalid or expired OTP.";
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
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <main>
        <div class="container auth-container">
            <div class="auth-form">
                <h1>Verify OTP</h1>
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="otp">Enter OTP</label>
                        <input type="text" id="otp" name="otp" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </form>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>