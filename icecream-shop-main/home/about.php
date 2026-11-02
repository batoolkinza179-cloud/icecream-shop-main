<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Blue Sky Summer Ice Cream</title>
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
            height: 400px;
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
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .hero p {
            font-size: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .about-content {
            padding: 80px 50px;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section {
            margin-bottom: 60px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .section h2 {
            color: #ff66a3;
            font-size: 32px;
            margin-bottom: 20px;
            border-bottom: 3px solid #ff66a3;
            padding-bottom: 10px;
            display: inline-block;
        }
        .section p {
            font-size: 18px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 20px;
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .value-card {
            text-align: center;
            padding: 30px;
            background: #fff9f9;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .value-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .value-card h3 {
            color: #ff66a3;
            margin-bottom: 15px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            text-align: center;
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #ff66a3;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 18px;
            color: #666;
        }
        .footer {
            background: #2c3e50;
            color: white;
            padding: 50px;
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
        <div>
            <h1>About Blue Sky Summer</h1>
            <p>Where every scoop tells a story of passion, quality, and happiness</p>
        </div>
    </section>
    
    <div class="about-content">
        <div class="container">
            <div class="section">
                <h2>Our Story</h2>
                <p>Welcome to Blue Sky Summer Ice Cream Shop, where we've been crafting joy since 2015. What started as a small family recipe has grown into a beloved destination for ice cream lovers across the region. Our journey began with a simple mission: to bring the joy and bliss of handcrafted, premium ice creams to every home.</p>
                <p>Every day, we wake up with one goal in mind - to create the perfect scoop that brings a smile to your face. Using traditional techniques combined with innovative flavors, we've created over 50 unique ice cream varieties that keep our customers coming back for more.</p>
            </div>
            
            <div class="section">
                <h2>Our Commitment to Quality</h2>
                <p>At Blue Sky Summer, quality isn't just a word - it's our promise. We source the finest ingredients from local farmers and trusted suppliers. Our milk comes from grass-fed cows, our fruits are hand-picked at peak ripeness, and we never use artificial flavors or preservatives. Every batch is made in small quantities to ensure perfection in every scoop.</p>
                
                <div class="stats" style="margin-top: 40px;">
                    <div>
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Unique Flavors</div>
                    </div>
                    <div>
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Natural Ingredients</div>
                    </div>
                    <div>
                        <div class="stat-number">10k+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Our Values</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">🌱</div>
                        <h3>Sustainability</h3>
                        <p>We use eco-friendly packaging and support local farmers, reducing our carbon footprint one scoop at a time.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">❤️</div>
                        <h3>Made with Love</h3>
                        <p>Every batch is crafted with passion and care, ensuring the perfect texture and flavor in every bite.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">🎨</div>
                        <h3>Innovation</h3>
                        <p>We constantly experiment with new flavors and combinations to surprise and delight our customers.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">🤝</div>
                        <h3>Community</h3>
                        <p>We believe in giving back and supporting the community that has supported us throughout our journey.</p>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Our Process</h2>
                <p>From farm to cone, we maintain the highest standards of quality. Our ice cream is made fresh daily in small batches, allowing us to control every aspect of production. We start with fresh milk and cream, add our secret blend of ingredients, and slowly churn to achieve that perfectly creamy texture you love.</p>
                <p>Whether it's our classic flavors or seasonal specials, each creation goes through rigorous taste testing before reaching your cone. We're not happy until you're happy!</p>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Blue Sky Summer Ice Cream Shop. All rights reserved.</p>
        <p style="margin-top: 10px;">Made with ❤️ and lots of ice cream</p>
    </footer>
</body>
</html>