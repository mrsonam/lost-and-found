<?php
session_start();
require_once 'config/database.php';

$error_messages = [];
$field_errors = [];

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

                // Redirect to intended page or home
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit();
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
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <div class="container auth-container">
            <div class="auth-form">
                <h1>Sign In</h1>
                <p>Welcome back! Please sign in to your account</p>

                <?php if (!empty($error_messages)): ?>
                    <div class="error-messages">
                        <?php foreach ($error_messages as $error): ?>
                            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group <?php echo isset($field_errors['email']) ? 'has-error' : ''; ?>">
                        <label for="email">Email Address</label>
                        <input id="email" name="email"
                            class="<?php echo isset($field_errors['email']) ? 'field-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <?php if (isset($field_errors['email'])): ?>
                            <span class="field-error-message"><?php echo htmlspecialchars($field_errors['email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?php echo isset($field_errors['password']) ? 'has-error' : ''; ?>">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                            class="<?php echo isset($field_errors['password']) ? 'field-error' : ''; ?>">
                        <?php if (isset($field_errors['password'])): ?>
                            <span class="field-error-message"><?php echo htmlspecialchars($field_errors['password']); ?></span>
                        <?php endif; ?>
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