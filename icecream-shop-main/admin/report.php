<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

$period = isset($_GET['period']) ? $_GET['period'] : 'today';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Set date ranges based on period
switch($period) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'yesterday':
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $end_date = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        break;
    case 'year':
        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');
        break;
    case 'custom':
        if (empty($start_date)) $start_date = date('Y-m-01');
        if (empty($end_date)) $end_date = date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
}

// Get real-time stats
$stats = [];

// Today's sales
$today_sql = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_price), 0) as total_revenue,
                AVG(total_price) as avg_order_value,
                COUNT(DISTINCT name) as unique_customers
              FROM orders 
              WHERE order_date = CURDATE()";
$today_result = $conn->query($today_sql);
$stats['today'] = $today_result->fetch_assoc();

// Weekly sales
$week_sql = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_price), 0) as total_revenue
              FROM orders 
              WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$week_result = $conn->query($week_sql);
$stats['week'] = $week_result->fetch_assoc();

// Monthly sales
$month_sql = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_price), 0) as total_revenue
              FROM orders 
              WHERE MONTH(order_date) = MONTH(CURDATE()) 
              AND YEAR(order_date) = YEAR(CURDATE())";
$month_result = $conn->query($month_sql);
$stats['month'] = $month_result->fetch_assoc();

// Yearly sales
$year_sql = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_price), 0) as total_revenue
              FROM orders 
              WHERE YEAR(order_date) = YEAR(CURDATE())";
$year_result = $conn->query($year_sql);
$stats['year'] = $year_result->fetch_assoc();

// Custom period sales report
$custom_sql = "SELECT 
                o.*,
                DATE_FORMAT(o.order_date, '%d-%m-%Y') as formatted_date,
                (SELECT COUNT(*) FROM orders WHERE order_date BETWEEN ? AND ?) as period_orders,
                (SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE order_date BETWEEN ? AND ?) as period_revenue
              FROM orders o 
              WHERE o.order_date BETWEEN ? AND ?
              ORDER BY o.order_date DESC";

$custom_stmt = $conn->prepare($custom_sql);
$custom_stmt->bind_param("ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
$custom_stmt->execute();
$custom_orders = $custom_stmt->get_result();

// Get period totals
$period_totals_sql = "SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_price), 0) as total_revenue,
                        AVG(total_price) as avg_order,
                        COUNT(DISTINCT name) as unique_customers,
                        MAX(total_price) as max_order,
                        MIN(total_price) as min_order
                      FROM orders 
                      WHERE order_date BETWEEN ? AND ?";
$period_stmt = $conn->prepare($period_totals_sql);
$period_stmt->bind_param("ss", $start_date, $end_date);
$period_stmt->execute();
$period_totals = $period_stmt->get_result()->fetch_assoc();

// Get hourly distribution for today
$hourly_sql = "SELECT 
                HOUR(order_date) as hour,
                COUNT(*) as orders,
                COALESCE(SUM(total_price), 0) as revenue
              FROM orders 
              WHERE order_date = CURDATE()
              GROUP BY HOUR(order_date)
              ORDER BY hour";
$hourly_data = $conn->query($hourly_sql);

// Get top products for period
$top_products_sql = "SELECT 
                        product_name,
                        COUNT(*) as order_count,
                        SUM(quantity) as total_quantity,
                        COALESCE(SUM(quantity * price), 0) as total_sales
                      FROM orders 
                      WHERE order_date BETWEEN ? AND ?
                      GROUP BY product_name
                      ORDER BY total_sales DESC
                      LIMIT 5";
$top_stmt = $conn->prepare($top_products_sql);
$top_stmt->bind_param("ss", $start_date, $end_date);
$top_stmt->execute();
$top_products = $top_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time Sales Report - Ice Cream Shop</title>
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
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        /* Period Selector */
        .period-selector {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .period-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .period-tab {
            padding: 10px 20px;
            background: #f8f9fa;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: 0.3s;
        }
        .period-tab:hover {
            background: #ec407a;
            color: white;
        }
        .period-tab.active {
            background: #ec407a;
            color: white;
        }
        .custom-date-form {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .date-group {
            flex: 1;
        }
        .date-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        .date-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ffcdd2;
            border-radius: 8px;
        }
        .btn-apply {
            padding: 10px 30px;
            background: #ec407a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            height: 42px;
        }
        .btn-apply:hover { background: #d81b60; }
        
        /* Quick Stats Cards */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ec407a, #f06292);
        }
        .stat-title {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-value {
            color: #2c3e50;
            font-size: 32px;
            font-weight: bold;
        }
        .stat-sub {
            color: #27ae60;
            font-size: 14px;
            margin-top: 5px;
        }
        
        /* Real-time Dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .card-header h3 {
            color: #ec407a;
            font-size: 18px;
        }
        .live-badge {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        /* Hourly Chart */
        .hourly-bars {
            margin-top: 15px;
        }
        .hour-bar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .hour-label {
            width: 50px;
            font-size: 12px;
            color: #7f8c8d;
        }
        .bar-container {
            flex: 1;
            height: 30px;
            background: #ecf0f1;
            border-radius: 15px;
            overflow: hidden;
            margin: 0 10px;
        }
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ec407a, #f06292);
            border-radius: 15px;
            transition: width 0.3s;
        }
        .bar-value {
            width: 80px;
            font-size: 12px;
            font-weight: bold;
            color: #27ae60;
        }
        
        /* Tables */
        .report-table {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #ec407a;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ffcdd2;
        }
        tr:hover { background: #f8f9fa; }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #cce5ff; color: #004085; }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .export-btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .export-btn:hover { background: #2980b9; }
        .export-btn.excel { background: #27ae60; }
        .export-btn.excel:hover { background: #229954; }
        .export-btn.pdf { background: #e74c3c; }
        .export-btn.pdf:hover { background: #c0392b; }
        
        .summary-numbers {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .number-box {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .number-box .label {
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .number-box .value {
            color: #ec407a;
            font-size: 24px;
            font-weight: bold;
        }
        
        @media (max-width: 1200px) {
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                display: none;
            }
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
                <li><a href="report.php" class="active">📈 Reports</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>📊 Real-time Sales Report</h1>
                <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
            </div>
            
            <!-- Period Selector -->
            <div class="period-selector">
                <div class="period-tabs">
                    <a href="?period=today" class="period-tab <?php echo $period == 'today' ? 'active' : ''; ?>">Today</a>
                    <a href="?period=yesterday" class="period-tab <?php echo $period == 'yesterday' ? 'active' : ''; ?>">Yesterday</a>
                    <a href="?period=week" class="period-tab <?php echo $period == 'week' ? 'active' : ''; ?>">This Week</a>
                    <a href="?period=month" class="period-tab <?php echo $period == 'month' ? 'active' : ''; ?>">This Month</a>
                    <a href="?period=year" class="period-tab <?php echo $period == 'year' ? 'active' : ''; ?>">This Year</a>
                    <a href="?period=custom" class="period-tab <?php echo $period == 'custom' ? 'active' : ''; ?>">Custom Range</a>
                </div>
                
                <?php if($period == 'custom'): ?>
                <form method="GET" class="custom-date-form">
                    <input type="hidden" name="period" value="custom">
                    <div class="date-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    <div class="date-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    <button type="submit" class="btn-apply">Apply</button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- Quick Stats Cards -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-title">Today's Orders</div>
                    <div class="stat-value"><?php echo $stats['today']['total_orders']; ?></div>
                    <div class="stat-sub">+ ₹<?php echo number_format($stats['today']['total_revenue'], 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">This Week</div>
                    <div class="stat-value"><?php echo $stats['week']['total_orders']; ?></div>
                    <div class="stat-sub">+ ₹<?php echo number_format($stats['week']['total_revenue'], 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">This Month</div>
                    <div class="stat-value"><?php echo $stats['month']['total_orders']; ?></div>
                    <div class="stat-sub">+ ₹<?php echo number_format($stats['month']['total_revenue'], 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">This Year</div>
                    <div class="stat-value"><?php echo $stats['year']['total_orders']; ?></div>
                    <div class="stat-sub">+ ₹<?php echo number_format($stats['year']['total_revenue'], 2); ?></div>
                </div>
            </div>
            
            <!-- Real-time Dashboard -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Hourly Sales (Today)</h3>
                        <span class="live-badge">🔴 LIVE</span>
                    </div>
                    <div class="hourly-bars">
                        <?php 
                        $max_hourly = 0;
                        $hourly_rows = [];
                        while($hour = $hourly_data->fetch_assoc()) {
                            $hourly_rows[] = $hour;
                            if($hour['revenue'] > $max_hourly) $max_hourly = $hour['revenue'];
                        }
                        
                        for($i = 0; $i < 24; $i++):
                            $hour_data = null;
                            foreach($hourly_rows as $h) {
                                if($h['hour'] == $i) {
                                    $hour_data = $h;
                                    break;
                                }
                            }
                            $percentage = $max_hourly > 0 ? (($hour_data['revenue'] ?? 0) / $max_hourly) * 100 : 0;
                        ?>
                        <div class="hour-bar">
                            <span class="hour-label"><?php echo sprintf('%02d:00', $i); ?></span>
                            <div class="bar-container">
                                <div class="bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                            </div>
                            <span class="bar-value">
                                ₹<?php echo number_format($hour_data['revenue'] ?? 0, 2); ?>
                                (<?php echo $hour_data['orders'] ?? 0; ?>)
                            </span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Period Summary</h3>
                        <span><?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?></span>
                    </div>
                    
                    <div class="summary-numbers">
                        <div class="number-box">
                            <div class="label">Total Orders</div>
                            <div class="value"><?php echo $period_totals['total_orders']; ?></div>
                        </div>
                        <div class="number-box">
                            <div class="label">Total Revenue</div>
                            <div class="value">₹<?php echo number_format($period_totals['total_revenue'], 2); ?></div>
                        </div>
                        <div class="number-box">
                            <div class="label">Avg Order</div>
                            <div class="value">₹<?php echo number_format($period_totals['avg_order'] ?? 0, 2); ?></div>
                        </div>
                        <div class="number-box">
                            <div class="label">Unique Customers</div>
                            <div class="value"><?php echo $period_totals['unique_customers']; ?></div>
                        </div>
                        <div class="number-box">
                            <div class="label">Highest Order</div>
                            <div class="value">₹<?php echo number_format($period_totals['max_order'] ?? 0, 2); ?></div>
                        </div>
                        <div class="number-box">
                            <div class="label">Lowest Order</div>
                            <div class="value">₹<?php echo number_format($period_totals['min_order'] ?? 0, 2); ?></div>
                        </div>
                    </div>
                    
                    <h4 style="color: #ec407a; margin: 20px 0 15px;">Top Products</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Orders</th>
                                <th>Quantity</th>
                                <th>Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($top = $top_products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($top['product_name']); ?></td>
                                <td><?php echo $top['order_count']; ?></td>
                                <td><?php echo $top['total_quantity']; ?></td>
                                <td>₹<?php echo number_format($top['total_sales'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Export Buttons -->
            <div class="export-buttons">
                <button onclick="exportToExcel()" class="export-btn excel">📊 Export to Excel</button>
                <button onclick="exportToCSV()" class="export-btn">📄 Export to CSV</button>
                <button onclick="window.print()" class="export-btn pdf">🖨️ Print Report</button>
            </div>
            
            <!-- Detailed Orders Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Orders (<?php echo $start_date; ?> to <?php echo $end_date; ?>)</h3>
                    <span><?php echo $period_totals['total_orders']; ?> orders</span>
                </div>
                
                <div class="report-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($custom_orders->num_rows > 0): ?>
                                <?php while($order = $custom_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $order['formatted_date']; ?></td>
                                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                    <td><?php echo $order['quantity']; ?></td>
                                    <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px;">
                                        No orders found for this period
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function exportToCSV() {
            const rows = document.querySelectorAll('table tr');
            const csv = [];
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                cols.forEach(col => {
                    let text = col.innerText;
                    // Handle commas in text
                    if (text.includes(',')) {
                        text = '"' + text + '"';
                    }
                    rowData.push(text);
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.download = 'sales_report_<?php echo $start_date; ?>_to_<?php echo $end_date; ?>.csv';
            link.click();
        }
        
        function exportToExcel() {
            // For Excel, we'll create an HTML table and save as .xls
            const tableHTML = document.querySelector('.report-table table').outerHTML;
            const blob = new Blob([tableHTML], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.download = 'sales_report_<?php echo $start_date; ?>_to_<?php echo $end_date; ?>.xls';
            link.click();
        }
        
        // Auto-refresh every 30 seconds for real-time data
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>