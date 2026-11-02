<?php
session_start();
require_once '../admin/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Build base query
$sql = "SELECT * FROM products WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
$params = [];
$count_params = [];
$types = "";
$count_types = "";

// Search conditions
if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND (product_name LIKE ? OR description LIKE ? OR ingredients LIKE ? OR category LIKE ? OR type LIKE ?)";
    $count_sql .= " AND (product_name LIKE ? OR description LIKE ? OR ingredients LIKE ? OR category LIKE ? OR type LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $types .= "sssss";
    $count_types .= "sssss";
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $count_sql .= " AND category = ?";
    $params[] = $category;
    $count_params[] = $category;
    $types .= "s";
    $count_types .= "s";
}

if (!empty($type)) {
    $sql .= " AND type = ?";
    $count_sql .= " AND type = ?";
    $params[] = $type;
    $count_params[] = $type;
    $types .= "s";
    $count_types .= "s";
}

// Price range checkboxes
if (!empty($price_range)) {
    switch($price_range) {
        case 'under_100':
            $sql .= " AND price < 100";
            $count_sql .= " AND price < 100";
            break;
        case '100_200':
            $sql .= " AND price BETWEEN 100 AND 200";
            $count_sql .= " AND price BETWEEN 100 AND 200";
            break;
        case '200_300':
            $sql .= " AND price BETWEEN 200 AND 300";
            $count_sql .= " AND price BETWEEN 200 AND 300";
            break;
        case '300_400':
            $sql .= " AND price BETWEEN 300 AND 400";
            $count_sql .= " AND price BETWEEN 300 AND 400";
            break;
        case 'above_400':
            $sql .= " AND price > 400";
            $count_sql .= " AND price > 400";
            break;
    }
}

// Sorting
switch($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY product_name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY product_name DESC";
        break;
    default:
        $sql .= " ORDER BY id DESC";
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get products for current page
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get unique categories and types
$categories = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
$types_list = $conn->query("SELECT DISTINCT type FROM products WHERE type IS NOT NULL AND type != '' ORDER BY type");

// Add to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }
    
    // Redirect with all current filters preserved
    $query_params = [];
    if (!empty($search)) $query_params['search'] = $search;
    if (!empty($category)) $query_params['category'] = $category;
    if (!empty($type)) $query_params['type'] = $type;
    if (!empty($price_range)) $query_params['price_range'] = $price_range;
    if ($sort != 'id_desc') $query_params['sort'] = $sort;
    if ($page > 1) $query_params['page'] = $page;
    $query_params['added'] = 1;
    
    $redirect_url = "menu.php?" . http_build_query($query_params);
    header("Location: $redirect_url");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ice Cream Menu</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: #f8f9fa;
            color: #2c3e50;
        }
        
        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header - Clean Design */
        .header {
            background: white;
            padding: 15px 25px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #ec407a;
            font-size: 1.8rem;
            font-weight: 600;
            letter-spacing: -0.5px;
        }
        
        .header h1 span {
            font-size: 2rem;
            margin-right: 8px;
        }
        
        /* Buttons - Clean Design */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 6px;
        }
        
        .btn-primary {
            background: #ec407a;
            color: white;
            box-shadow: 0 4px 10px rgba(236,64,122,0.2);
        }
        
        .btn-primary:hover {
            background: #d81b60;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(236,64,122,0.3);
        }
        
        .btn-secondary {
            background: #3498db;
            color: white;
            box-shadow: 0 4px 10px rgba(52,152,219,0.2);
        }
        
        .btn-secondary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: white;
            color: #ec407a;
            border: 2px solid #ec407a;
        }
        
        .btn-outline:hover {
            background: #ec407a;
            color: white;
        }
        
        .cart-badge {
            background: white;
            color: #ec407a;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        /* Success Message - Clean */
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 16px;
            border-left: 5px solid #4caf50;
            box-shadow: 0 4px 12px rgba(76,175,80,0.1);
        }
        
        /* Filter Section - Clean Card Design */
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        
        .filter-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .filter-title span {
            font-size: 1.8rem;
        }
        
        /* Filter Grid - Clean Layout */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Inputs and Selects - Clean Style */
        .filter-group input,
        .filter-group select {
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            background: white;
            width: 100%;
            transition: all 0.2s ease;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #ec407a;
            outline: none;
            box-shadow: 0 0 0 4px rgba(236,64,122,0.1);
        }
        
        /* Search Input with Icon */
        .search-wrapper {
            position: relative;
        }
        
        .search-wrapper input {
            padding-left: 45px;
        }
        
        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #999;
        }
        
        /* Price Range - Clean Checkbox Design */
        .price-range-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 16px;
        }
        
        .price-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: white;
            border-radius: 50px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .price-checkbox:hover {
            border-color: #ec407a;
            background: #fff5f8;
        }
        
        .price-checkbox input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #ec407a;
            margin: 0;
        }
        
        .price-checkbox span {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .price-checkbox .price-count {
            color: #ec407a;
            font-size: 13px;
            font-weight: 600;
            margin-left: auto;
        }
        
        /* Active Filters - Clean Tags */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
            padding: 10px 0;
        }
        
        .filter-tag {
            background: #ec407a;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(236,64,122,0.2);
        }
        
        .filter-tag a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
        }
        
        .filter-tag a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Filter Actions */
        .filter-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        
        /* Results Info - Clean */
        .results-info {
            background: white;
            padding: 15px 25px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .results-count {
            color: #666;
            font-size: 15px;
        }
        
        .results-count strong {
            color: #ec407a;
            font-size: 20px;
            font-weight: 700;
        }
        
        .sort-select {
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .sort-select:hover {
            border-color: #ec407a;
        }
        
        /* Products Grid - Clean Cards */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .menu-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(236,64,122,0.15);
        }
        
        .menu-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }
        
        .menu-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ec407a;
            color: white;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(236,64,122,0.3);
        }
        
        .menu-content {
            padding: 20px;
        }
        
        .menu-title {
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .menu-category {
            color: #ec407a;
            font-size: 0.85rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .menu-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .menu-ingredients {
            color: #888;
            font-size: 0.85rem;
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 12px;
            line-height: 1.5;
        }
        
        .menu-price {
            color: #ec407a;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        /* Add to Cart Form - Clean */
        .add-to-cart-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .quantity-input {
            width: 80px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            text-align: center;
            font-weight: 600;
        }
        
        .quantity-input:focus {
            border-color: #ec407a;
            outline: none;
        }
        
        .cart-btn {
            flex: 1;
            padding: 14px;
            background: #ec407a;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(236,64,122,0.2);
        }
        
        .cart-btn:hover {
            background: #d81b60;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(236,64,122,0.3);
        }
        
        /* Floating Cart Button - Clean */
        .cart-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 16px 30px;
            background: #ec407a;
            color: white;
            text-decoration: none;
            border-radius: 60px;
            box-shadow: 0 10px 25px rgba(236,64,122,0.4);
            font-weight: 600;
            font-size: 16px;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .cart-button:hover {
            transform: scale(1.05);
            background: #d81b60;
            box-shadow: 0 15px 35px rgba(236,64,122,0.5);
        }
        
        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #ec407a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Pagination - Clean */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 40px 0 20px;
        }
        
        .page-link {
            padding: 12px 18px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .page-link:hover {
            border-color: #ec407a;
            color: #ec407a;
            transform: translateY(-2px);
        }
        
        .page-link.active {
            background: #ec407a;
            color: white;
            border-color: #ec407a;
        }
        
        /* No Results - Clean */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            grid-column: 1 / -1;
        }
        
        .no-results h3 {
            color: #ec407a;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        
        .no-results p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        
        /* Highlight search term */
        .highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 1.4rem;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .price-range-group {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .filter-actions .btn {
                width: 100%;
            }
            
            .results-info {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .sort-select {
                width: 100%;
            }
            
            .cart-button {
                bottom: 15px;
                right: 15px;
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><span>🍦</span> Ice Cream Menu</h1>
            <div style="display: flex; gap: 10px;">
                <a href="user_dashboard.php" class="btn btn-secondary">← Dashboard</a>
                <?php 
                $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                ?>
                <a href="cart.php" class="btn btn-primary">
                    🛒 Cart <span class="cart-badge"><?php echo $cart_count; ?></span>
                </a>
            </div>
        </div>
        
        <!-- Success Message -->
        <?php if (isset($_GET['added'])): ?>
            <div class="success-message">
                ✅ Item added to cart successfully!
            </div>
        <?php endif; ?>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-title">
                <span>🔍</span>
                <h2>Search & Filter Products</h2>
            </div>
            
            <form method="GET" id="filterForm">
                <div class="filter-grid">
                    <!-- Search Box with Icon -->
                    <div class="filter-group">
                        <label>Search</label>
                        <div class="search-wrapper">
                            <span class="search-icon">🔍</span>
                            <input type="search" name="search" placeholder="Type to search..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   id="searchInput"
                                   autocomplete="off">
                        </div>
                    </div>
                    
                    <!-- Category Dropdown -->
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category" id="categorySelect">
                            <option value="">All Categories</option>
                            <?php 
                            $categories->data_seek(0);
                            while($cat = $categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                    <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Type Dropdown -->
                    <div class="filter-group">
                        <label>Type</label>
                        <select name="type" id="typeSelect">
                            <option value="">All Types</option>
                            <?php 
                            $types_list->data_seek(0);
                            while($t = $types_list->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($t['type']); ?>" 
                                    <?php echo $type == $t['type'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['type']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Price Range - Clean Checkbox Design -->
                <div style="margin-top: 15px;">
                    <label style="font-weight: 600; color: #555; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: block;">Price Range</label>
                    <div class="price-range-group">
                        <label class="price-checkbox">
                            <input type="radio" name="price_range" value="under_100" 
                                <?php echo $price_range == 'under_100' ? 'checked' : ''; ?>
                                class="price-radio">
                            <span>Under ₹100</span>
                            <?php
                            $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE price < 100")->fetch_assoc()['c'];
                            ?>
                            <span class="price-count"><?php echo $count; ?></span>
                        </label>
                        
                        <label class="price-checkbox">
                            <input type="radio" name="price_range" value="100_200" 
                                <?php echo $price_range == '100_200' ? 'checked' : ''; ?>
                                class="price-radio">
                            <span>₹100 - ₹200</span>
                            <?php
                            $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE price BETWEEN 100 AND 200")->fetch_assoc()['c'];
                            ?>
                            <span class="price-count"><?php echo $count; ?></span>
                        </label>
                        
                        <label class="price-checkbox">
                            <input type="radio" name="price_range" value="200_300" 
                                <?php echo $price_range == '200_300' ? 'checked' : ''; ?>
                                class="price-radio">
                            <span>₹200 - ₹300</span>
                            <?php
                            $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE price BETWEEN 200 AND 300")->fetch_assoc()['c'];
                            ?>
                            <span class="price-count"><?php echo $count; ?></span>
                        </label>
                        
                        <label class="price-checkbox">
                            <input type="radio" name="price_range" value="300_400" 
                                <?php echo $price_range == '300_400' ? 'checked' : ''; ?>
                                class="price-radio">
                            <span>₹300 - ₹400</span>
                            <?php
                            $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE price BETWEEN 300 AND 400")->fetch_assoc()['c'];
                            ?>
                            <span class="price-count"><?php echo $count; ?></span>
                        </label>
                        
                        <label class="price-checkbox">
                            <input type="radio" name="price_range" value="above_400" 
                                <?php echo $price_range == 'above_400' ? 'checked' : ''; ?>
                                class="price-radio">
                            <span>Above ₹400</span>
                            <?php
                            $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE price > 400")->fetch_assoc()['c'];
                            ?>
                            <span class="price-count"><?php echo $count; ?></span>
                        </label>
                        
                        <label class="price-checkbox">
                            <input type="radio" name="price_range" value="" 
                                <?php echo empty($price_range) ? 'checked' : ''; ?>
                                class="price-radio">
                            <span>All Prices</span>
                            <?php
                            $count = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
                            ?>
                            <span class="price-count"><?php echo $count; ?></span>
                        </label>
                    </div>
                </div>
                
                <!-- Active Filters -->
                <?php if(!empty($search) || !empty($category) || !empty($type) || !empty($price_range)): ?>
                <div class="active-filters">
                    <span style="color: #666; font-size: 14px;">Active filters:</span>
                    <?php if(!empty($search)): ?>
                        <span class="filter-tag">
                            "<?php echo htmlspecialchars($search); ?>"
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['search']);
                                echo http_build_query($params);
                            ?>">✕</a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if(!empty($category)): ?>
                        <span class="filter-tag">
                            <?php echo htmlspecialchars($category); ?>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['category']);
                                echo http_build_query($params);
                            ?>">✕</a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if(!empty($type)): ?>
                        <span class="filter-tag">
                            <?php echo htmlspecialchars($type); ?>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['type']);
                                echo http_build_query($params);
                            ?>">✕</a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if(!empty($price_range)): 
                        $price_labels = [
                            'under_100' => 'Under ₹100',
                            '100_200' => '₹100 - ₹200',
                            '200_300' => '₹200 - ₹300',
                            '300_400' => '₹300 - ₹400',
                            'above_400' => 'Above ₹400'
                        ];
                    ?>
                        <span class="filter-tag">
                            <?php echo $price_labels[$price_range]; ?>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['price_range']);
                                echo http_build_query($params);
                            ?>">✕</a>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Hidden inputs -->
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>" id="sortInput">
                <input type="hidden" name="page" value="1" id="pageInput">
                
                <!-- Filter Actions -->
                <div class="filter-actions">
                    <a href="menu.php" class="btn btn-outline">Clear All Filters</a>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
        
        <!-- Results Info -->
        <div class="results-info">
            <div class="results-count">
                <strong id="totalCount"><?php echo $total_rows; ?></strong> products found
                <?php if(!empty($search)): ?>
                    <span style="color: #ec407a; margin-left: 10px;">for "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>
            
            <form method="GET" id="sortForm">
                <?php 
                foreach(['search', 'category', 'type', 'price_range'] as $param) {
                    if(isset($_GET[$param]) && !empty($_GET[$param])) {
                        echo '<input type="hidden" name="' . $param . '" value="' . htmlspecialchars($_GET[$param]) . '">';
                    }
                }
                ?>
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="id_desc" <?php echo $sort == 'id_desc' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                    <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                </select>
            </form>
        </div>
        
        <!-- Products Grid -->
        <div class="menu-grid" id="productsGrid">
            <?php if($products->num_rows > 0): ?>
                <?php while($item = $products->fetch_assoc()): ?>
                <div class="menu-card" data-product-id="<?php echo $item['id']; ?>">
                    <?php if(!empty($item['category'])): ?>
                        <span class="menu-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                    <?php endif; ?>
                    
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                         class="menu-image"
                         loading="lazy">
                    
                    <div class="menu-content">
                        <h3 class="menu-title"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        
                        <?php if(!empty($item['type'])): ?>
                            <div class="menu-category">
                                <span>🍨</span> <?php echo htmlspecialchars($item['type']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <p class="menu-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        
                        <?php if(!empty($item['ingredients'])): ?>
                            <div class="menu-ingredients">
                                <strong>Ingredients:</strong> <?php echo htmlspecialchars($item['ingredients']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="menu-price">₹<?php echo number_format($item['price'], 2); ?></div>
                        
                        <form method="POST" class="add-to-cart-form">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="add_to_cart" value="1">
                            
                            <!-- Preserve filters -->
                            <?php if(!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <?php if(!empty($category)): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                            <?php endif; ?>
                            <?php if(!empty($type)): ?>
                                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                            <?php endif; ?>
                            <?php if(!empty($price_range)): ?>
                                <input type="hidden" name="price_range" value="<?php echo htmlspecialchars($price_range); ?>">
                            <?php endif; ?>
                            <?php if($sort != 'id_desc'): ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <?php endif; ?>
                            <?php if($page > 1): ?>
                                <input type="hidden" name="page" value="<?php echo $page; ?>">
                            <?php endif; ?>
                            
                            <input type="number" name="quantity" min="1" value="1" class="quantity-input">
                            <button type="submit" class="cart-btn">Add to Cart</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>😢 No products found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                    <a href="menu.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="pagination" id="pagination">
            <?php if($page > 1): ?>
                <a href="?<?php 
                    $params = $_GET;
                    $params['page'] = $page - 1;
                    echo http_build_query($params);
                ?>" class="page-link">←</a>
            <?php endif; ?>
            
            <?php for($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++): ?>
                <a href="?<?php 
                    $params = $_GET;
                    $params['page'] = $i;
                    echo http_build_query($params);
                ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="?<?php 
                    $params = $_GET;
                    $params['page'] = $page + 1;
                    echo http_build_query($params);
                ?>" class="page-link">→</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Floating Cart Button -->
        <a href="cart.php" class="cart-button">
            🛒 Cart (<?php echo $cart_count; ?>)
        </a>
    </div>
    
    <script>
        // Get elements
        const searchInput = document.getElementById('searchInput');
        const categorySelect = document.getElementById('categorySelect');
        const typeSelect = document.getElementById('typeSelect');
        const priceRadios = document.querySelectorAll('.price-radio');
        const sortSelect = document.querySelector('.sort-select');
        const filterForm = document.getElementById('filterForm');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const productsGrid = document.getElementById('productsGrid');
        const totalCountSpan = document.getElementById('totalCount');
        
        // Real-time search - items appear as you type
        let searchTimeout;
        
        function performSearch() {
            // Show loading
            loadingOverlay.style.display = 'flex';
            
            // Get current form data
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            
            // Fetch new results
            fetch(`menu_ajax.php?${params}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update products grid
                productsGrid.innerHTML = data.html;
                
                // Update total count
                if (totalCountSpan) {
                    totalCountSpan.textContent = data.total;
                }
                
                // Update pagination if exists
                if (data.pagination) {
                    const paginationDiv = document.getElementById('pagination');
                    if (paginationDiv) {
                        paginationDiv.innerHTML = data.pagination;
                    }
                }
                
                // Hide loading
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                loadingOverlay.style.display = 'none';
            });
        }
        
        // Search input - real-time
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Reset to page 1
                    document.getElementById('pageInput').value = 1;
                    performSearch();
                }, 300); // 300ms delay for smooth typing
            });
        }
        
        // Category select - real-time
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                document.getElementById('pageInput').value = 1;
                performSearch();
            });
        }
        
        // Type select - real-time
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                document.getElementById('pageInput').value = 1;
                performSearch();
            });
        }
        
        // Price radios - real-time
        priceRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('pageInput').value = 1;
                performSearch();
            });
        });
        
        // Sort select - real-time
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                document.getElementById('sortInput').value = this.value;
                document.getElementById('pageInput').value = 1;
                performSearch();
            });
        }
        
        // Pagination links - use AJAX
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('page-link') && !e.target.classList.contains('active')) {
                e.preventDefault();
                const url = new URL(e.target.href);
                const page = url.searchParams.get('page');
                
                if (page) {
                    document.getElementById('pageInput').value = page;
                    performSearch();
                }
            }
        });
        
        // Prevent form submission for AJAX filters
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
    </script>
</body>
</html>