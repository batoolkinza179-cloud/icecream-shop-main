<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

// Update order status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $message = "Order status updated successfully!";
    } else {
        $error = "Error updating order: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all orders with details
$sql = "SELECT o.*, 
        DATE_FORMAT(o.order_date, '%d-%m-%Y') as formatted_date 
        FROM orders o 
        ORDER BY o.id DESC";
$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - Ice Cream Shop</title>
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
        }
        .sidebar ul { list-style: none; }
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
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 32px;
        }
        .back-btn {
            padding: 10px 20px;
            background: #ec407a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .back-btn:hover { background: #d81b60; }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            color: #ec407a;
            font-size: 28px;
            font-weight: bold;
        }
        .orders-table-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #ec407a;
            color: white;
            padding: 15px;
            text-align: left;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #ffcdd2;
        }
        tr:hover { background: #f8f9fa; }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        .status-delivered {
            background: #cce5ff;
            color: #004085;
        }
        select {
            padding: 8px;
            border: 2px solid #ffcdd2;
            border-radius: 5px;
            margin-right: 10px;
        }
        .update-btn {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .update-btn:hover { background: #2980b9; }
        .filter-section {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }
        .filter-btn {
            padding: 10px 20px;
            background: #ecf0f1;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .filter-btn.active {
            background: #ec407a;
            color: white;
        }
        .filter-btn:hover { background: #bdc3c7; }
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
            <div class="header">
                <h1>📋 Order Management</h1>
                <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php
            // Calculate statistics
            $total_orders = $orders->num_rows;
            $pending = 0;
            $shipped = 0;
            $delivered = 0;
            $total_revenue = 0;
            
            $orders->data_seek(0);
            while($row = $orders->fetch_assoc()) {
                switch($row['status']) {
                    case 'Pending': $pending++; break;
                    case 'Shipped': $shipped++; break;
                    case 'Delivered': $delivered++; break;
                }
                $total_revenue += $row['total_price'];
            }
            $orders->data_seek(0);
            ?>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $total_orders; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <div class="number" style="color: #f39c12;"><?php echo $pending; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Shipped</h3>
                    <div class="number" style="color: #3498db;"><?php echo $shipped; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Delivered</h3>
                    <div class="number" style="color: #27ae60;"><?php echo $delivered; ?></div>
                </div>
            </div>
            
            <div class="filter-section">
                <button class="filter-btn active" onclick="filterOrders('all')">All</button>
                <button class="filter-btn" onclick="filterOrders('Pending')">Pending</button>
                <button class="filter-btn" onclick="filterOrders('Shipped')">Shipped</button>
                <button class="filter-btn" onclick="filterOrders('Delivered')">Delivered</button>
            </div>
            
            <div class="orders-table-container">
                <table id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $orders->fetch_assoc()): ?>
                        <tr data-status="<?php echo $order['status']; ?>">
                            <td>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td><?php echo htmlspecialchars(substr($order['address'], 0, 30)) . '...'; ?></td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo $order['formatted_date']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status">
                                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" name="update_status" class="update-btn">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function filterOrders(status) {
            const rows = document.querySelectorAll('#ordersTable tbody tr');
            const buttons = document.querySelectorAll('.filter-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>