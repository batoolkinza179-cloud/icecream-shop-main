<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

// Get statistics
$stats = [];
$queries = [
    'orders' => "SELECT COUNT(*) as count FROM orders",
    'products' => "SELECT COUNT(*) as count FROM products",
    'users' => "SELECT COUNT(*) as count FROM users",
    'revenue' => "SELECT COALESCE(SUM(total_price), 0) as total FROM orders"
];

foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    $stats[$key] = $result->fetch_assoc()['count'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ice Cream Shop</title>
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
        }
        .sidebar img {
            display: block;
            margin: 0 auto 30px;
            width: 150px;
            height: auto;
        }
        .sidebar ul { list-style: none; }
        .sidebar li { margin: 5px 0; }
        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: #ecf0f1;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
            padding-left: 35px;
        }
        .main-content {
            flex: 1;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            color: #2c3e50;
            font-size: 36px;
            font-weight: bold;
        }
        .stat-card .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <img src="../image/logo.png" alt="Logo">
            <ul>
                <li><a href="admin_dashboard.php">📊 Dashboard</a></li>
                <li><a href="manage_products.php">📦 Products</a></li>
                <li><a href="view_orders.php">📋 Orders</a></li>
                <li><a href="report.php">📈 Reports</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="welcome-card">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">📦</div>
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $stats['orders']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">🍦</div>
                    <h3>Total Products</h3>
                    <div class="number"><?php echo $stats['products']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">👥</div>
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $stats['users']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">💰</div>
                    <h3>Total Revenue</h3>
                    <div class="number">₹<?php echo number_format($stats['revenue'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>