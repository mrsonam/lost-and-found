<?php
session_start();
require_once 'config/database.php';

$error_message = '';

// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Process login form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if both fields are filled
    if (empty($email) || empty($password)) {
        error_log("Login Failed - Missing email or password at " . date('Y-m-d H:i:s'));
        $error_message = 'Please enter both email and password.';
    } else {
        $connection = getDBConnection();

        // Find user by email in database
        $user = getSingleRow($connection, "SELECT id, email, password_hash, first_name, last_name FROM users WHERE email = ? AND is_active = 1", "s", [$email]);

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
                $error_message = 'Login failed. Please try again.';
            } else {
                $_SESSION['session_token'] = $session_token;
                error_log("Login Success - User ID: " . $user['id'] . " logged in successfully at " . date('Y-m-d H:i:s'));

                // Redirect to intended page or home
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            if (!$user) {
                error_log("Login Failed - User not found or inactive for email: " . $email . " at " . date('Y-m-d H:i:s'));
            } else {
                error_log("Login Failed - Invalid password for email: " . $email . " at " . date('Y-m-d H:i:s'));
            }
            $error_message = 'Invalid email or password.';
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
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <div class="container auth-container">
            <div class="auth-form">
                <h1>Sign In</h1>
                <p>Welcome back! Please sign in to your account</p>

                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full-width">Sign In</button>
                </form>

                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php">Create one here</a></p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>