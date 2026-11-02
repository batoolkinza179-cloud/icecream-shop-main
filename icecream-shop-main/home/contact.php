<?php
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Name, email and message are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Here you would typically send an email or save to database
        $success = "Thank you, $name! Your message has been received. We'll get back to you soon!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Blue Sky Summer Ice Cream</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #ff66a3 0%, #ff3385 100%);
            padding: 15px 50px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo { height: 50px; }
        .nav { display: flex; gap: 25px; }
        .nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 25px;
            transition: 0.3s;
        }
        .nav a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        .hero {
            height: 250px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../image/bg1.webp');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero h1 {
            font-size: 48px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .contact-section {
            padding: 60px 50px;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
        }
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .contact-info {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .contact-info h2 {
            color: #ff66a3;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px;
            background: #fff5f5;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .info-item:hover {
            transform: translateX(10px);
            background: #ffe6e6;
        }
        .info-icon {
            font-size: 24px;
            width: 50px;
            height: 50px;
            background: #ff66a3;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .info-text h3 {
            color: #333;
            margin-bottom: 5px;
        }
        .info-text p {
            color: #666;
        }
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .contact-form h2 {
            color: #ff66a3;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input, textarea, select {
            width: 100%;
            padding: 15px;
            border: 2px solid #ffcdd2;
            border-radius: 10px;
            font-size: 16px;
            transition: 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #ff66a3;
            outline: none;
            box-shadow: 0 0 10px rgba(255,102,163,0.2);
        }
        textarea {
            height: 150px;
            resize: vertical;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #ff66a3;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #ff3385;
            transform: scale(1.02);
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .map-section {
            padding: 0 50px 60px 50px;
        }
        .map-container {
            max-width: 1200px;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        iframe {
            width: 100%;
            height: 400px;
            border: none;
        }
        .business-hours {
            margin-top: 30px;
            background: #fff5f5;
            padding: 20px;
            border-radius: 10px;
        }
        .hours-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ffcdd2;
        }
        .hours-item:last-child {
            border-bottom: none;
        }
        .footer {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="../image/logo.png" alt="Ice Cream Shop Logo" class="logo">
        <nav class="nav">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="location.html">Location</a>
            <a href="contact.php">Contact</a>
            <a href="../user/login.php">Login</a>
        </nav>
    </header>
    
    <section class="hero">
        <h1>Get in Touch</h1>
    </section>
    
    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-info">
                <h2>📍 Visit Us</h2>
                
                <div class="info-item">
                    <div class="info-icon">📍</div>
                    <div class="info-text">
                        <h3>Address</h3>
                        <p>123 Blue Sky Summer, Ranipur<br>sindh - 627002</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">📞</div>
                    <div class="info-text">
                        <h3>Phone</h3>
                        <p>+91 98765 43210<br>+91 98765 43211</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">✉️</div>
                    <div class="info-text">
                        <h3>Email</h3>
                        <p>hello@blueskysummer.com<br>support@blueskysummer.com</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">⏰</div>
                    <div class="info-text">
                        <h3>Business Hours</h3>
                        <div class="business-hours">
                            <div class="hours-item">
                                <span>Monday - Friday</span>
                                <span>10:00 AM - 10:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span>Saturday - Sunday</span>
                                <span>11:00 AM - 11:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <h2>📧 Send us a Message</h2>
                
                <?php if ($success): ?>
                    <div class="message success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name *" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email *" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Your Phone Number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <select name="subject">
                            <option value="">Select Subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="order">Order Related</option>
                            <option value="feedback">Feedback</option>
                            <option value="complaint">Complaint</option>
                            <option value="catering">Catering</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Your Message *" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    <button type="submit">Send Message</button>
                </form>
            </div>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28367.81113977971!2d68.48001029470467!3d27.282615183303268!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3935951ff43c7823%3A0x6b1e589c22f04e7b!2sRanipur%20Riyasat%20Ranipur%2C%20Pakistan!5e0!3m2!1sen!2s!4v1793346829448!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>
    </section>
    
    <section class="map-section">
        <div class="map-container">
            
        </div>
    </section>
    
    <footer class="footer">
        <p>&copy; 2026 Blue Sky Summer Ice Cream Shop. All rights reserved.</p>
    </footer>
</body>
</html>