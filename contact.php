<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Lost & Found</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header>
        <h1>Lost & Found</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="report.html">Report Item</a></li>
                <li><a href="browse.html">Browse Items</a></li>
                <li><a href="contact.html" class="active">Contact Us</a></li>
            </ul>
        </nav>
    </header>

    <main class="contact-container">
        <section class="contact-info">
            <h2>Contact Us</h2>
            <p>If you have lost or found an item, please reach out to us using the form below or visit our office.</p>
            <ul>
                <li><strong>Phone:</strong> +61 400 123 456</li>
                <li><strong>Email:</strong> support@lostandfound.com</li>
                <li><strong>Address:</strong> 123 Civic Square, Canberra, ACT, Australia</li>
            </ul>
        </section>

        <section class="contact-form">
            <form action="#" method="post">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" placeholder="Enter subject" required>

                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" placeholder="Write your message..." required></textarea>

                <button type="submit">Send Message</button>
            </form>
        </section>

        <section class="map">
            <h2>Our Location</h2>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.926386232854!2d149.12655681522157!3d-35.28130458030171!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b164d1f73b3a1b1%3A0x3b16c76f1c469861!2sCanberra%20ACT!5e0!3m2!1sen!2sau!4v1636685000000!5m2!1sen!2sau"
                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Lost & Found. All Rights Reserved.</p>
    </footer>
</body>
</html>
