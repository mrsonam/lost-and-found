<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Us - Lost & Found</title>
  <link rel="stylesheet" href="css/styles.css"> <!-- global styles -->
</head>

<body>
  <?php include 'components/navbar.php'; ?>

  <main>
    <!-- Hero -->
    <section class="contact-hero">
      <div class="contact-hero-inner">
        <h1>Contact Us</h1>
        <p>Have a question or found something? Let’s reconnect people with their belongings.</p>
      </div>
    </section>

    <!-- Contact Info Cards -->
    <section class="contact-info-grid">
      <div class="container">
        <div class="info-card fade-in-up delay-1">
          <img src="images/email-icon.png" alt="Email">
          <h3>Email</h3>
          <p>support@lostfound.com</p>
        </div>
        <div class="info-card fade-in-up delay-2">
          <img src="images/phone-icon.png" alt="Phone">
          <h3>Phone</h3>
          <p>+61 400 123 456</p>
        </div>
        <div class="info-card fade-in-up delay-3">
          <img src="images/location-icon.png" alt="Address">
          <h3>Address</h3>
          <p>123 City Centre, Canberra, ACT</p>
        </div>
      </div>
    </section>

    <!-- Contact Form -->
    <section class="contact-form-section">
      <div class="container">
        <div class="contact-form-card fade-in-left">
          <h2>Send a Message</h2>
          <form action="#" method="post" class="floating-form">
            <div class="form-group">
              <input type="text" id="name" name="name" required>
              <label for="name">Full Name</label>
            </div>
            <div class="form-group">
              <input type="email" id="email" name="email" required>
              <label for="email">Email Address</label>
            </div>
            <div class="form-group">
              <input type="text" id="subject" name="subject" required>
              <label for="subject">Subject</label>
            </div>
            <div class="form-group">
              <textarea id="message" name="message" rows="5" required></textarea>
              <label for="message">Message</label>
            </div>
            <button type="submit" class="btn-animated">Send Message</button>
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
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2820.3609062566065!2d149.12519157531958!3d-35.27618419332263!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b164d0022e8ae85%3A0x8fbc2978e5330668!2sWentworth%20Institute%20of%20Higher%20Education!5e1!3m2!1sen!2sau!4v1757292671781!5m2!1sen!2sau" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </section>
  </main>

  <?php include 'components/footer.php'; ?>
</body>

</html>
