<?php
session_start();
require_once 'config/database.php';

// === PHPMailer includes ===
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_messages = [];
$field_errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1 — Collect form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Step 2 — Validate form data with field-specific errors
    if (empty($email)) {
        $field_errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($first_name)) {
        $field_errors['first_name'] = 'First name is required.';
    }

    if (empty($last_name)) {
        $field_errors['last_name'] = 'Last name is required.';
    }

    if (empty($password)) {
        $field_errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $field_errors['password'] = 'Password must be at least 6 characters long.';
    }

    if (empty($confirm_password)) {
        $field_errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $field_errors['confirm_password'] = 'Passwords do not match.';
    }

    // Only proceed if no field errors
    if (empty($field_errors)) {
        $connection = getDBConnection();

        // Step 3 — Check if email exists
        $existing_user = getSingleRow($connection, "SELECT id FROM users WHERE email = ?", "s", [$email]);

        if ($existing_user) {
            $field_errors['email'] = 'An account with this email already exists.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Step 4 — Generate OTP
            $otp = rand(100000, 999999);
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            // Step 5 — Store user + OTP in DB
            $result = executeQuery(
                $connection,
                "INSERT INTO users (email, password_hash, first_name, last_name, phone, otp_code, otp_expires) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                "sssssss",
                [$email, $password_hash, $first_name, $last_name, $phone, $otp, $otp_expiry]
            );

            if ($result) {
                // Step 6 — Send OTP email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->SMTPDebug = 0; // change to 2 for debugging
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'bidit.infodev@gmail.com'; // your Gmail address
                    $mail->Password   = 'xuad dnam xznr opuw';   // Gmail app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('bidit.infodev@gmail.com', 'Lost & Found System');
                    $mail->addAddress($email, $first_name . ' ' . $last_name);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP Verification Code';
                    $mail->Body = <<<EOD
                                    <div style="font-family: Arial, sans-serif; line-height: 1.5;">
                                    <h2 style="color: #2E86C1;">Lost & Found - OTP Verification</h2>
                                    <p>Hello <strong>{$first_name}</strong>,</p>
                                    <p>Thank you for registering with <strong>Lost & Found</strong>.</p>
                                    <p>Your <strong>One-Time Password (OTP)</strong> is:</p>
                                    <h1 style="color: #2E86C1;">{$otp}</h1>
                                    <p>This OTP is valid for <strong>10 minutes</strong>.</p>
                                    <p>Please enter this code on the registration page to verify your email address.</p>
                                    <br>
                                    <p style="font-size: 0.9em; color: gray;">If you did not request this, please ignore this email.</p>
                                    <hr>
                                    <p style="font-size: 0.8em;">Lost & Found Team<br>support@lostandfound.com</p>
                                    </div>
                                EOD;


                    $mail->send();

                    // Store email in session for OTP verification
                    $_SESSION['pending_email'] = $email;

                    // Correct redirect path
                    header("Location: /lost-and-found/verify_otp.php");
                    exit();
                } catch (Exception $e) {
                    $error_messages[] = "Could not send OTP email. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error_messages[] = 'Registration failed. Please try again.';
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

                <?php if (!empty($error_messages)): ?>
                    <div class="error-messages">
                        <?php foreach ($error_messages as $error): ?>
                            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="form-group <?php echo isset($field_errors['email']) ? 'has-error' : ''; ?>">
                        <label>Email Address *</label>
                        <input type="email" name="email"
                            class="<?php echo isset($field_errors['email']) ? 'field-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <?php if (isset($field_errors['email'])): ?>
                            <span class="field-error-message"><?php echo htmlspecialchars($field_errors['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($field_errors['first_name']) ? 'has-error' : ''; ?>">
                        <label>First Name *</label>
                        <input type="text" name="first_name"
                            class="<?php echo isset($field_errors['first_name']) ? 'field-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                        <?php if (isset($field_errors['first_name'])): ?>
                            <span class="field-error-message"><?php echo htmlspecialchars($field_errors['first_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($field_errors['last_name']) ? 'has-error' : ''; ?>">
                        <label>Last Name *</label>
                        <input type="text" name="last_name"
                            class="<?php echo isset($field_errors['last_name']) ? 'field-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                        <?php if (isset($field_errors['last_name'])): ?>
                            <span class="field-error-message"><?php echo htmlspecialchars($field_errors['last_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    </div>
                    <div class="form-group <?php echo isset($field_errors['password']) ? 'has-error' : ''; ?>">
                        <label>Password *</label>
                        <input type="password" name="password"
                            class="<?php echo isset($field_errors['password']) ? 'field-error' : ''; ?>" required>
                        <?php if (isset($field_errors['password'])): ?>
                            <span class="field-error-message"><?php echo htmlspecialchars($field_errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($field_errors['confirm_password']) ? 'has-error' : ''; ?>">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password"
                            class="<?php echo isset($field_errors['confirm_password']) ? 'field-error' : ''; ?>" required>
                        <?php if (isset($field_errors['confirm_password'])): ?>
                            <span class="field-error-message"><?php echo htmlspecialchars($field_errors['confirm_password']); ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>

                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>