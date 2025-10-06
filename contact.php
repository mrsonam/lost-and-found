<?php
// Include database config
require_once "config.php";

$showSuccess = false; // flag to trigger popup

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name    = $conn->real_escape_string($_POST['name']);
  $email   = $conn->real_escape_string($_POST['email']);
  $phone   = $conn->real_escape_string($_POST['phone']);
  $subject = $conn->real_escape_string($_POST['subject']);
  $message = $conn->real_escape_string($_POST['message']);

  // Insert query
  $sql = "INSERT INTO contact (name, email, phone, subject, message)
            VALUES ('$name', '$email', '$phone', '$subject', '$message')";

  if ($conn->query($sql) === TRUE) {
    $showSuccess = true; // trigger popup
  } else {
    echo "Error: " . $conn->error;
  }
}

$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Us - Lost & Found</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css"> <!-- global styles -->

</head>

<body>
  <?php include 'includes/navbar.php'; ?>

  <?php if ($showSuccess): ?>
    <!-- Success Popup -->
    <div id="success-popup" class="popup-message">
      Message sent successfully!
    </div>
  <?php endif; ?>

  <main>
    <!-- Modern Hero Section -->
    <section class="contact-hero-modern">
      <div class="contact-hero-background">
        <div class="contact-hero-pattern"></div>
      </div>
      <div class="container contact-hero-content">
        <div class="contact-hero-text">
          <h1 class="contact-hero-title">Contact Us</h1>
          <p class="contact-hero-subtitle">Have a question or found something? Let's reconnect people with their belongings</p>
        </div>
      </div>
    </section>

    <!-- Contact Info Cards -->
    <section class="contact-info-grid">
      <div class="container">
        <div class="info-card fade-in-up delay-1">
          <img src="images/email-icon.png" alt="Email">
          <h3>Email</h3>
          <p>support@lostfound.com.au</p>
        </div>
        <div class="info-card fade-in-up delay-2">
          <img src="images/phone-icon.png" alt="Phone">
          <h3>Phone</h3>
          <p>+61 123 456 789</p>
        </div>
        <div class="info-card fade-in-up delay-3">
          <img src="images/location-icon.png" alt="Address">
          <h3>Address</h3>
          <p>123 City Centre, Canberra, ACT</p>
        </div>
      </div>
    </section>

    <!-- Contact Form -->
    <section class="contact-form-section-modern">
      <div class="container">
        <div class="contact-form-card">
          <div class="contact-form-header">
            <h2>Send a Message</h2>
            <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible</p>
          </div>
          <form action="contact.php" method="post" class="floating-form" novalidate>
            <div class="form-group">
              <input type="text" id="name" name="name" placeholder=" " required>
              <label for="name">Full Name</label>
            </div>
            <div class="form-group">
              <input type="email" id="email" name="email" placeholder=" " required>
              <label for="email">Email Address</label>
            </div>
            <div class="form-group">
              <input type="tel" id="phone" name="phone" placeholder=" " required>
              <label for="phone">Phone Number</label>
            </div>
            <div class="form-group">
              <input type="text" id="subject" name="subject" placeholder=" " required>
              <label for="subject">Subject</label>
            </div>
            <div class="form-group">
              <textarea id="message" name="message" rows="5" placeholder=" " required></textarea>
              <label for="message">Message</label>
            </div>
            <button type="submit" class="btn btn-primary btn-large">
              <span>Send Message</span>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </form>
        </div>
      </div>
    </section>

    <!-- Map -->
    <section class="contact-map-section fade-in-up">
      <div class="container">
        <h2>Find Us</h2>
        <div class="map-wrapper">
          <iframe class="map-frame"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2820.3609062566065!2d149.12519157531958!3d-35.27618419332263!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b164d0022e8ae85%3A0x8fbc2978e5330668!2sWentworth%20Institute%20of%20Higher%20Education!5e1!3m2!1sen!2sau!4v1757641786471!5m2!1sen!2sau"
            width="600" height="450" style="border:0;" allowfullscreen="Yes" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      <?php if ($showSuccess): ?>
        // Toast will auto-animate via CSS, no manual control needed
      <?php endif; ?>

      // Contact form specific validation
      const contactForm = document.querySelector('.floating-form');
      if (contactForm) {
        const submitButton = contactForm.querySelector('button[type="submit"]');

        contactForm.addEventListener('submit', function(e) {
          e.preventDefault();

          // Get all required fields
          const requiredFields = contactForm.querySelectorAll('input[required], textarea[required]');
          let isValid = true;

          // Clear previous errors
          requiredFields.forEach(field => {
            field.classList.remove('field-error');
            const formGroup = field.closest('.form-group');
            if (formGroup) {
              formGroup.classList.remove('has-error');
            }
            // Remove existing error messages
            const existingError = formGroup.querySelector('.field-error-message');
            if (existingError) {
              existingError.remove();
            }
          });

          // Validate each required field
          requiredFields.forEach(field => {
            const formGroup = field.closest('.form-group');

            if (!field.value.trim()) {
              isValid = false;
              field.classList.add('field-error');
              if (formGroup) {
                formGroup.classList.add('has-error');
              }

              // Add error message
              const errorMessage = document.createElement('span');
              errorMessage.className = 'field-error-message';
              errorMessage.textContent = 'This field is required.';
              formGroup.appendChild(errorMessage);
            }
          });

          // Validate email format
          const emailField = contactForm.querySelector('input[type="email"]');
          if (emailField && emailField.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value.trim())) {
              isValid = false;
              emailField.classList.add('field-error');
              const formGroup = emailField.closest('.form-group');
              if (formGroup) {
                formGroup.classList.add('has-error');
                const existingError = formGroup.querySelector('.field-error-message');
                if (existingError) {
                  existingError.textContent = 'Please enter a valid email address.';
                } else {
                  const errorMessage = document.createElement('span');
                  errorMessage.className = 'field-error-message';
                  errorMessage.textContent = 'Please enter a valid email address.';
                  formGroup.appendChild(errorMessage);
                }
              }
            }
          }

          // Validate phone format (basic validation)
          const phoneField = contactForm.querySelector('input[type="tel"]');
          if (phoneField && phoneField.value.trim()) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,}$/;
            if (!phoneRegex.test(phoneField.value.trim())) {
              isValid = false;
              phoneField.classList.add('field-error');
              const formGroup = phoneField.closest('.form-group');
              if (formGroup) {
                formGroup.classList.add('has-error');
                const existingError = formGroup.querySelector('.field-error-message');
                if (existingError) {
                  existingError.textContent = 'Please enter a valid phone number.';
                } else {
                  const errorMessage = document.createElement('span');
                  errorMessage.className = 'field-error-message';
                  errorMessage.textContent = 'Please enter a valid phone number.';
                  formGroup.appendChild(errorMessage);
                }
              }
            }
          }

          if (isValid) {
            // If all validations pass, submit the form
            contactForm.submit();
          } else {
            // Scroll to first error
            const firstError = contactForm.querySelector('.field-error');
            if (firstError) {
              firstError.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
              });
              firstError.focus();
            }
          }
        });
      }
    });
  </script>
</body>

</html>