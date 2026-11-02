<?php
session_start();
require_once '../admin/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get user's order count
$user_id = $_SESSION['id'];
$sql = "SELECT COUNT(*) as order_count FROM orders WHERE name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$order_count = $stmt->get_result()->fetch_assoc()['order_count'];

// Get user's recent orders
$recent_orders_sql = "SELECT * FROM orders WHERE name = ? ORDER BY id DESC LIMIT 3";
$recent_stmt = $conn->prepare($recent_orders_sql);
$recent_stmt->bind_param("s", $_SESSION['username']);
$recent_stmt->execute();
$recent_orders = $recent_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Ice Cream Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: #2c3e50;
            color: white;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar img {
            display: block;
            margin: 0 auto 30px;
            width: 150px;
        }
        .sidebar ul { list-style: none; }
        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: #ecf0f1;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #34495e;
            padding-left: 35px;
            border-left: 4px solid #ec407a;
        }
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .welcome-card h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .welcome-card p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        /* Success Message Styles */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 16px;
            border-left: 5px solid #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .success-message span {
            font-size: 24px;
        }
        
        .featured-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .feature-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .feature-card:hover { transform: translateY(-5px); }
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .feature-card h3 {
            color: #ec407a;
            margin-bottom: 10px;
        }
        .feature-card p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        .feature-btn {
            display: inline-block;
            padding: 10px 25px;
            background: #ec407a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .feature-btn:hover { background: #d81b60; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-box h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .stat-box .number {
            color: #ec407a;
            font-size: 24px;
            font-weight: bold;
        }
        
        .recent-orders {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .recent-orders h2 {
            color: #ec407a;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ffcdd2;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-info h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .order-info p {
            color: #7f8c8d;
            font-size: 13px;
        }
        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-Pending { background: #fff3cd; color: #856404; }
        .status-Shipped { background: #d4edda; color: #155724; }
        .status-Delivered { background: #cce5ff; color: #004085; }
        
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <img src="../image/logo.png" alt="Logo">
            <ul>
                <li><a href="user_dashboard.php" class="active">🏠 Dashboard</a></li>
                <li><a href="menu.php">🍨 Menu</a></li>
                <li><a href="cart.php">🛒 Cart</a></li>
                <li><a href="update_profile.php">👤 Profile</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <!-- Success Message Display -->
            <?php if(isset($_GET['update']) && $_GET['update'] == 'success'): ?>
                <div class="success-message">
                    <span>✅</span> Profile updated successfully!
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['update_success'])): ?>
                <div class="success-message">
                    <span>✅</span> <?php echo $_SESSION['update_success']; ?>
                </div>
                <?php unset($_SESSION['update_success']); ?>
            <?php endif; ?>

            <?php if(isset($_GET['order']) && $_GET['order'] == 'placed'): ?>
                <div class="success-message">
                    <span>🍦</span> Order placed successfully! Thank you for your purchase.
                </div>
            <?php endif; ?>
            
            <div class="welcome-card">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
                <p>Ready for some delicious ice cream? Check out our latest flavors!</p>
            </div>
            
            <div class="featured-section">
                <div class="feature-card">
                    <div class="feature-icon">🍨</div>
                    <h3>Browse Menu</h3>
                    <p>Explore our delicious ice cream flavors with new search & filter options</p>
                    <a href="menu.php" class="feature-btn">View Menu</a>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛒</div>
                    <h3>Your Cart</h3>
                    <p>Check your selected items and proceed to checkout</p>
                    <a href="cart.php" class="feature-btn">View Cart</a>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👤</div>
                    <h3>Profile</h3>
                    <p>Update your account details and password</p>
                    <a href="update_profile.php" class="feature-btn">Edit Profile</a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $order_count; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Member Since</h3>
                    <div class="number">2024</div>
                </div>
                <div class="stat-box">
                    <h3>Status</h3>
                    <div class="number">Active</div>
                </div>
            </div>
            
            <div class="recent-orders">
                <h2>📋 Recent Orders</h2>
                <?php if($recent_orders->num_rows > 0): ?>
                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <h4><?php echo htmlspecialchars($order['product_name']); ?> (x<?php echo $order['quantity']; ?>)</h4>
                                <p>Order #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?> • ₹<?php echo number_format($order['total_price'], 2); ?></p>
                            </div>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #7f8c8d; padding: 20px;">
                        No orders yet. <a href="menu.php" style="color: #ec407a;">Start shopping!</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>