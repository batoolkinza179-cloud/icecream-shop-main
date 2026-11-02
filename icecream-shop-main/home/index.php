<?php
$featured_products = [
    [
        'name' => 'Strawberry Delight',
        'description' => 'A rich and creamy strawberry ice cream made with fresh strawberries.',
        'price' => 200,
        'image' => '../image/strawberry.jpeg'
    ],
    [
        'name' => 'Pink Vanilla',
        'description' => 'Classic vanilla ice cream with a delightful pink twist.',
        'price' => 150,
        'image' => '../image/pinkvanilla.jpeg'
    ],
    [
        'name' => 'Cherry Blossom',
        'description' => 'A delightful cherry ice cream with a hint of blossom flavor.',
        'price' => 250,
        'image' => '../image/cherryblossom.webp'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blue Sky Summer - Ice Cream Shop</title>
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
        .logo {
            height: 50px;
            transition: transform 0.3s;
        }
        .logo:hover { transform: scale(1.1); }
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
            height: 90vh;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('../image/slider.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero-content {
            max-width: 800px;
            padding: 30px;
            animation: fadeIn 1.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hero h1 {
            font-size: 56px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .hero p {
            font-size: 24px;
            margin-bottom: 30px;
        }
        .hero-btn {
            padding: 15px 40px;
            background: #ff66a3;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            border: 2px solid white;
        }
        .hero-btn:hover {
            background: #ff3385;
            transform: scale(1.1);
        }
        .featured {
            padding: 80px 50px;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
        }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-title h2 {
            color: #ff66a3;
            font-size: 42px;
            margin-bottom: 15px;
        }
        .section-title p {
            color: #666;
            font-size: 18px;
        }
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .featured-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .featured-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .card-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .card-content {
            padding: 25px;
        }
        .card-content h3 {
            color: #ff66a3;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .card-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .price {
            color: #ff3385;
            font-size: 28px;
            font-weight: bold;
        }
        .about-section {
            padding: 80px 50px;
            background: white;
        }
        .about-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        .about-text h3 {
            color: #ff66a3;
            font-size: 36px;
            margin-bottom: 20px;
        }
        .about-text p {
            color: #666;
            line-height: 1.8;
            font-size: 18px;
        }
        .about-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .footer {
            background: #2c3e50;
            color: white;
            padding: 50px;
            text-align: center;
        }
        .social-links {
            margin-top: 20px;
        }
        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 24px;
            text-decoration: none;
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
            <a href="../admin/admin_login.php">Admin</a>
        </nav>
    </header>
    
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Blue Sky Summer</h1>
            <p>Experience the joy of handcrafted ice creams made with love and the finest ingredients</p>
            <button class="hero-btn" onclick="window.location.href='../user/menu.php'">Explore Our Menu</button>
        </div>
    </section>
    
    <section class="featured">
        <div class="section-title">
            <h2>Our Special Flavors</h2>
            <p>Handpicked just for you</p>
        </div>
        <div class="featured-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="featured-card">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="card-image">
                <div class="card-content">
                    <h3><?php echo $product['name']; ?></h3>
                    <p><?php echo $product['description']; ?></p>
                    <span class="price">₹<?php echo number_format($product['price'], 2); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <section class="about-section">
        <div class="about-content">
            <div class="about-text">
                <h3>Our Story</h3>
                <p>At Blue Sky Summer Ice Cream Shop, we believe in creating moments of happiness and indulgence. 
                Our passion for ice cream is rooted in a commitment to quality and innovation. We source the 
                freshest ingredients and blend them with love to craft flavors that are both classic and unique.</p>
                <p style="margin-top: 20px;">From the rich, creamy texture to the burst of flavors, every scoop is a 
                testament to our dedication to excellence.</p>
            </div>
            <div class="about-image">
                <img src="../image/about_bg.jpg" alt="Our Ice Cream Shop">
            </div>
        </div>
    </section>
    
    <footer class="footer">
        <p>&copy; 2026 Blue Sky Summer Ice Cream Shop. All rights reserved.</p>
        <div class="social-links">
            <a href="#">📘</a>
            <a href="#">📷</a>
            <a href="#">🐦</a>
        </div>
    </footer>
</body>
</html>