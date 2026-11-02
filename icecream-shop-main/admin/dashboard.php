<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status='completed'");
$stats['total_revenue'] = $result->fetch_assoc()['total'];

$recent_orders = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");

$low_stock = $conn->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px 0;
        }
        .sidebar-header {
            text-align: center;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header img {
            width: 120px;
            margin-bottom: 15px;
        }
        .sidebar-header h3 {
            font-size: 18px;
            opacity: 0.9;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: 0.3s;
            gap: 15px;
        }
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            padding-left: 35px;
        }
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #ec407a;
        }
        .sidebar-menu .icon {
            font-size: 20px;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .header h1 {
            color: #333;
            font-size: 28px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .logout-btn {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        .stat-info h3 {
            color: #999;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .stat-info .number {
            color: #333;
            font-size: 28px;
            font-weight: bold;
        }
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .chart-card h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .recent-orders {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .recent-orders h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .stock-warning {
            color: #e74c3c;
            font-weight: 600;
        }
        .view-all {
            display: inline-block;
            margin-top: 15px;
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="<?php echo ASSETS_URL; ?>images/logo.png" alt="<?php echo SITE_NAME; ?>">
                <h3>Welcome, <?php echo $_SESSION['admin_username']; ?></h3>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="products.php">
                    <span class="icon">🍦</span>
                    <span>Manage Products</span>
                </a>
                <a href="orders.php">
                    <span class="icon">📋</span>
                    <span>View Orders</span>
                </a>
                <a href="reports.php">
                    <span class="icon">📈</span>
                    <span>Reports</span>
                </a>
                <a href="logout.php">
                    <span class="icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span><?php echo date('l, F j, Y'); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <div class="number"><?php echo $stats['total_orders']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🍦</div>
                    <div class="stat-info">
                        <h3>Total Products</h3>
                        <div class="number"><?php echo $stats['total_products']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <div class="number"><?php echo $stats['total_users']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <div class="number">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="charts-row">
                <div class="chart-card">
                    <h2>Recent Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['order_number']; ?></td>
                                <td><?php echo $order['customer_name']; ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <a href="orders.php" class="view-all">View All Orders →</a>
                </div>
                
                <div class="chart-card">
                    <h2>Low Stock Alert</h2>
                    <?php if ($low_stock->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = $low_stock->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['name']; ?></td>
                                    <td class="stock-warning"><?php echo $product['stock']; ?> left</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: #27ae60; text-align: center; padding: 20px;">✓ All products have sufficient stock</p>
                    <?php endif; ?>
                    <a href="products.php" class="view-all">Manage Products →</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>