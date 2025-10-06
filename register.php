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
$showSuccess = false;

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

                    // Set success flag for toast display
                    $showSuccess = true;
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php if ($showSuccess): ?>
        <!-- Success Popup -->
        <div id="success-popup" class="popup-message">
            Registration successful! Please check your email for OTP verification.
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
                    <h1 class="auth-hero-title">Join Our Community</h1>
                    <p class="auth-hero-subtitle">Create your account and start helping reunite lost items with their owners</p>
                </div>
                <div class="auth-form-container">
                    <div class="auth-form-card">
                        <div class="auth-form-header">
                            <h2>Create Account</h2>
                            <p>Join our community to report lost and found items</p>
                        </div>

                        <?php if (!empty($error_messages)): ?>
                            <div class="error-messages">
                                <?php foreach ($error_messages as $error): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" novalidate class="floating-form">
                            <div class="form-group <?php echo isset($field_errors['email']) ? 'has-error' : ''; ?>">
                                <input type="email" name="email" required placeholder=" "
                                    class="<?php echo isset($field_errors['email']) ? 'field-error' : ''; ?>"
                                    value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                <label>Email Address</label>
                                <?php if (isset($field_errors['email'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['email']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group <?php echo isset($field_errors['first_name']) ? 'has-error' : ''; ?>">
                                <input type="text" name="first_name" required placeholder=" "
                                    class="<?php echo isset($field_errors['first_name']) ? 'field-error' : ''; ?>"
                                    value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                                <label>First Name</label>
                                <?php if (isset($field_errors['first_name'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['first_name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group <?php echo isset($field_errors['last_name']) ? 'has-error' : ''; ?>">
                                <input type="text" name="last_name" required placeholder=" "
                                    class="<?php echo isset($field_errors['last_name']) ? 'field-error' : ''; ?>"
                                    value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                                <label>Last Name</label>
                                <?php if (isset($field_errors['last_name'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['last_name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <input type="tel" name="phone" placeholder=" " value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                                <label>Phone Number</label>
                            </div>
                            <div class="form-group <?php echo isset($field_errors['password']) ? 'has-error' : ''; ?>">
                                <input type="password" name="password" required placeholder=" "
                                    class="<?php echo isset($field_errors['password']) ? 'field-error' : ''; ?>">
                                <label>Password</label>
                                <?php if (isset($field_errors['password'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['password']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group <?php echo isset($field_errors['confirm_password']) ? 'has-error' : ''; ?>">
                                <input type="password" name="confirm_password" required placeholder=" "
                                    class="<?php echo isset($field_errors['confirm_password']) ? 'field-error' : ''; ?>">
                                <label>Confirm Password</label>
                                <?php if (isset($field_errors['confirm_password'])): ?>
                                    <span class="field-error-message"><?php echo htmlspecialchars($field_errors['confirm_password']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-large">
                                <span>Create Account</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </form>

                        <div class="auth-links">
                            <p>Already have an account? <a href="login.php">Sign in here</a></p>
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

                // Redirect to OTP verification after 3 seconds
                setTimeout(() => {
                    window.location.href = "verify_otp.php";
                }, 3000);
            <?php endif; ?>
        });
    </script>
</body>

</html>