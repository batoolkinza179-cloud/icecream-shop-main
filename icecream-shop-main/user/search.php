<?php
require_once '../includes/config.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

if (!empty($search_query)) {
    if (!isset($_SESSION['recent_searches'])) {
        $_SESSION['recent_searches'] = [];
    }
    array_unshift($_SESSION['recent_searches'], $search_query);
    $_SESSION['recent_searches'] = array_unique($_SESSION['recent_searches']);
    $_SESSION['recent_searches'] = array_slice($_SESSION['recent_searches'], 0, 8);
}

$results = null;
if (!empty($search_query) || !empty($category)) {
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($search_query)) {
        $sql .= " AND (name LIKE ? OR description LIKE ? OR ingredients LIKE ?)";
        $search_term = "%$search_query%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "sss";
    }
    
    if (!empty($category)) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    $sql .= " ORDER BY is_featured DESC, name ASC LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();
}

$categories = $conn->query("SELECT DISTINCT category, COUNT(*) as count FROM products GROUP BY category ORDER BY count DESC");

$trending = $conn->query("SELECT p.*, COUNT(o.id) as order_count 
                          FROM products p 
                          LEFT JOIN order_items o ON p.id = o.product_id 
                          GROUP BY p.id 
                          ORDER BY order_count DESC 
                          LIMIT 6");

$category_icons = [
    'Chocolate' => '🍫',
    'Fruit' => '🍓',
    'Classic' => '🍦',
    'Nut' => '🌰',
    'Premium' => '✨',
    'Sugar-Free' => '💚'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Search - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
        }
        .app-container {
            max-width: 480px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .header {
            position: sticky;
            top: 0;
            background: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid #f0f0f0;
            z-index: 100;
        }
        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #333;
            font-size: 20px;
            transition: 0.3s;
        }
        .back-btn:active {
            background: #e0e0e0;
        }
        .header h1 {
            flex: 1;
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
        .cart-icon {
            position: relative;
            text-decoration: none;
            color: #333;
            font-size: 24px;
        }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ec407a;
            color: white;
            font-size: 11px;
            padding: 4px 6px;
            border-radius: 12px;
            min-width: 18px;
            text-align: center;
        }
        .search-section {
            padding: 15px 20px;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }
        .search-box {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 30px;
            padding: 5px 20px;
            border: 2px solid transparent;
            transition: 0.3s;
        }
        .search-box:focus-within {
            border-color: #ec407a;
            background: white;
            box-shadow: 0 5px 15px rgba(236,64,122,0.2);
        }
        .search-icon {
            color: #999;
            font-size: 20px;
            margin-right: 10px;
        }
        .search-box input {
            flex: 1;
            padding: 15px 0;
            border: none;
            background: transparent;
            font-size: 16px;
            outline: none;
        }
        .clear-btn {
            background: none;
            border: none;
            color: #999;
            font-size: 18px;
            cursor: pointer;
            padding: 5px 10px;
            display: <?php echo empty($search_query) ? 'none' : 'block'; ?>;
        }
        .section {
            padding: 20px;
            border-bottom: 10px solid #f8f9fa;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .section-header h2 {
            font-size: 16px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .clear-all {
            color: #ec407a;
            text-decoration: none;
            font-size: 14px;
        }
        .recent-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .recent-item {
            background: #f8f9fa;
            padding: 12px 18px;
            border-radius: 30px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            border: 1px solid transparent;
        }
        .recent-item:active {
            background: #ec407a;
            color: white;
            transform: scale(0.95);
        }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 10px;
        }
        .category-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px 5px;
            text-align: center;
            text-decoration: none;
            transition: 0.2s;
        }
        .category-card:active {
            background: #ec407a;
            transform: scale(0.95);
        }
        .category-card:active .category-name {
            color: white;
        }
        .category-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .category-name {
            font-size: 12px;
            font-weight: 500;
            color: #333;
        }
        .trending-scroll {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding: 5px 0 15px;
            scrollbar-width: none;
        }
        .trending-scroll::-webkit-scrollbar {
            display: none;
        }
        .trending-card {
            min-width: 130px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-decoration: none;
            border: 1px solid #f0f0f0;
            transition: 0.2s;
        }
        .trending-card:active {
            transform: scale(0.95);
            border-color: #ec407a;
        }
        .trending-image {
            width: 100%;
            height: 100px;
            object-fit: cover;
        }
        .trending-info {
            padding: 12px;
        }
        .trending-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .trending-price {
            font-size: 16px;
            color: #ec407a;
            font-weight: bold;
        }
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }
        .results-count {
            color: #666;
            font-size: 14px;
        }
        .filter-btn {
            background: none;
            border: 1px solid #e0e0e0;
            padding: 8px 15px;
            border-radius: 20px;
            color: #333;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }
        .filter-btn:active {
            background: #ec407a;
            color: white;
            border-color: #ec407a;
        }
        .results-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 20px;
        }
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
            transition: 0.2s;
            position: relative;
        }
        .product-card:active {
            transform: scale(0.98);
        }
        .product-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .discount-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #ec407a;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .quick-add {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            background: #ec407a;
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            transition: 0.2s;
            border: 2px solid white;
        }
        .product-card:hover .quick-add,
        .product-card:active .quick-add {
            opacity: 1;
        }
        .product-info {
            padding: 12px;
        }
        .product-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .product-category {
            font-size: 11px;
            color: #999;
            margin-bottom: 8px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .current-price {
            font-size: 16px;
            font-weight: bold;
            color: #ec407a;
        }
        .original-price {
            font-size: 12px;
            color: #999;
            text-decoration: line-through;
            margin-left: 5px;
        }
        .add-btn {
            background: #ec407a;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .add-btn:active {
            transform: scale(1.05);
            background: #d81b60;
        }
        .no-results {
            text-align: center;
            padding: 40px 20px;
        }
        .no-results-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .no-results h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .no-results p {
            color: #999;
            margin-bottom: 20px;
        }
        .suggestion-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            max-width: 480px;
            margin: 0 auto;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            border-top: 1px solid #f0f0f0;
            z-index: 100;
        }
        .nav-item {
            text-decoration: none;
            color: #999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 12px;
        }
        .nav-item.active {
            color: #ec407a;
        }
        .nav-icon {
            font-size: 22px;
        }
        .keyboard {
            padding: 20px;
            background: #f8f9fa;
            margin-bottom: 70px;
        }
        .keyboard-row {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
        }
        .key {
            flex: 1;
            height: 45px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 500;
            color: #333;
            cursor: pointer;
            transition: 0.1s;
            box-shadow: 0 2px 0 #e0e0e0;
        }
        .key:active {
            transform: translateY(2px);
            box-shadow: none;
            background: #ec407a;
            color: white;
        }
        .space-bar {
            height: 45px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #666;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 0 #e0e0e0;
        }
        .space-bar:active {
            transform: translateY(2px);
            box-shadow: none;
            background: #ec407a;
            color: white;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="header">
            <a href="dashboard.php" class="back-btn">←</a>
            <h1>Search</h1>
            <?php $cart_count = getCartCount(); ?>
            <a href="cart.php" class="cart-icon">
                🛒
                <?php if($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="search-section">
            <form method="GET" id="searchForm">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="q" placeholder="Search 'Strawberry'" value="<?php echo htmlspecialchars($search_query); ?>" autofocus>
                    <button type="button" class="clear-btn" onclick="clearSearch()">✕</button>
                </div>
            </form>
        </div>
        
        <?php if (empty($search_query) && empty($category)): ?>
            <?php if (!empty($_SESSION['recent_searches'])): ?>
            <div class="section">
                <div class="section-header">
                    <h2>Recent Searches</h2>
                    <a href="clear_searches.php" class="clear-all">Clear</a>
                </div>
                <div class="recent-grid">
                    <?php foreach ($_SESSION['recent_searches'] as $search): ?>
                    <a href="?q=<?php echo urlencode($search); ?>" class="recent-item">
                        🔍 <?php echo htmlspecialchars($search); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="section">
                <div class="section-header">
                    <h2>Shop by Category</h2>
                </div>
                <div class="categories-grid">
                    <?php while($cat = $categories->fetch_assoc()): 
                        $icon = isset($category_icons[$cat['category']]) ? $category_icons[$cat['category']] : '🍨';
                    ?>
                    <a href="?category=<?php echo urlencode($cat['category']); ?>" class="category-card">
                        <div class="category-icon"><?php echo $icon; ?></div>
                        <div class="category-name"><?php echo $cat['category']; ?></div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="section">
                <div class="section-header">
                    <h2>⚡ Trending Now</h2>
                </div>
                <div class="trending-scroll">
                    <?php while($trend = $trending->fetch_assoc()): ?>
                    <a href="product.php?id=<?php echo $trend['id']; ?>" class="trending-card">
                        <img src="<?php echo $trend['image']; ?>" alt="<?php echo $trend['name']; ?>" class="trending-image">
                        <div class="trending-info">
                            <div class="trending-name"><?php echo $trend['name']; ?></div>
                            <div class="trending-price">₹<?php echo number_format($trend['price'], 2); ?></div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="results-header">
                <span class="results-count">
                    <?php 
                    $count = $results ? $results->num_rows : 0;
                    echo $count . ' result' . ($count != 1 ? 's' : '');
                    ?>
                </span>
                <button class="filter-btn" onclick="alert('Filter feature coming soon!')">
                    <span>⚡</span> Filter
                </button>
            </div>
            
            <?php if ($results && $results->num_rows > 0): ?>
                <div class="results-grid">
                    <?php while($item = $results->fetch_assoc()): 
                        $discount = $item['discount_price'] ? round((($item['price'] - $item['discount_price']) / $item['price']) * 100) : 0;
                    ?>
                    <div class="product-card">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-image">
                        <?php if($discount > 0): ?>
                            <span class="discount-badge">-<?php echo $discount; ?>%</span>
                        <?php endif; ?>
                        <button class="quick-add" onclick="quickAdd(<?php echo $item['id']; ?>)">+</button>
                        <div class="product-info">
                            <div class="product-name"><?php echo $item['name']; ?></div>
                            <div class="product-category"><?php echo $item['category']; ?></div>
                            <div class="price-row">
                                <span>
                                    <span class="current-price">₹<?php echo number_format($item['discount_price'] ?: $item['price'], 2); ?></span>
                                    <?php if($item['discount_price']): ?>
                                        <span class="original-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <?php endif; ?>
                                </span>
                                <form method="POST" action="cart.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <button type="submit" class="add-btn">ADD</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">🔍</div>
                    <h3>No results found</h3>
                    <p>Try searching for something else</p>
                    <div class="suggestion-chips">
                        <a href="?q=Strawberry" class="recent-item">🍓 Strawberry</a>
                        <a href="?q=Chocolate" class="recent-item">🍫 Chocolate</a>
                        <a href="?q=Vanilla" class="recent-item">🍦 Vanilla</a>
                        <a href="?q=Mango" class="recent-item">🥭 Mango</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="keyboard">
            <div class="keyboard-row">
                <div class="key" onclick="addToSearch('1')">1</div>
                <div class="key" onclick="addToSearch('2')">2</div>
                <div class="key" onclick="addToSearch('3')">3</div>
                <div class="key" onclick="addToSearch('4')">4</div>
                <div class="key" onclick="addToSearch('5')">5</div>
                <div class="key" onclick="addToSearch('6')">6</div>
                <div class="key" onclick="addToSearch('7')">7</div>
                <div class="key" onclick="addToSearch('8')">8</div>
                <div class="key" onclick="addToSearch('9')">9</div>
                <div class="key" onclick="addToSearch('0')">0</div>
            </div>
            <div class="keyboard-row">
                <div class="key" onclick="addToSearch('Q')">Q</div>
                <div class="key" onclick="addToSearch('W')">W</div>
                <div class="key" onclick="addToSearch('E')">E</div>
                <div class="key" onclick="addToSearch('R')">R</div>
                <div class="key" onclick="addToSearch('T')">T</div>
                <div class="key" onclick="addToSearch('Y')">Y</div>
                <div class="key" onclick="addToSearch('U')">U</div>
                <div class="key" onclick="addToSearch('I')">I</div>
                <div class="key" onclick="addToSearch('O')">O</div>
                <div class="key" onclick="addToSearch('P')">P</div>
            </div>
            <div class="keyboard-row">
                <div class="key" onclick="addToSearch('A')">A</div>
                <div class="key" onclick="addToSearch('S')">S</div>
                <div class="key" onclick="addToSearch('D')">D</div>
                <div class="key" onclick="addToSearch('F')">F</div>
                <div class="key" onclick="addToSearch('G')">G</div>
                <div class="key" onclick="addToSearch('H')">H</div>
                <div class="key" onclick="addToSearch('J')">J</div>
                <div class="key" onclick="addToSearch('K')">K</div>
                <div class="key" onclick="addToSearch('L')">L</div>
            </div>
            <div class="keyboard-row">
                <div class="key" onclick="addToSearch('Z')">Z</div>
                <div class="key" onclick="addToSearch('X')">X</div>
                <div class="key" onclick="addToSearch('C')">C</div>
                <div class="key" onclick="addToSearch('V')">V</div>
                <div class="key" onclick="addToSearch('B')">B</div>
                <div class="key" onclick="addToSearch('N')">N</div>
                <div class="key" onclick="addToSearch('M')">M</div>
                <div class="key wide" onclick="addToSearch('?123')">?123</div>
            </div>
            <div class="space-bar" onclick="addToSearch(' ')">Space</div>
        </div>
        
        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span>Home</span>
            </a>
            <a href="search.php" class="nav-item active">
                <span class="nav-icon">🔍</span>
                <span>Search</span>
            </a>
            <a href="menu.php" class="nav-item">
                <span class="nav-icon">🍦</span>
                <span>Menu</span>
            </a>
            <a href="cart.php" class="nav-item">
                <span class="nav-icon">🛒</span>
                <span>Cart</span>
                <?php if($cart_count > 0): ?>
                    <span style="background: #ec407a; color: white; padding: 2px 6px; border-radius: 12px; font-size: 10px;"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php" class="nav-item">
                <span class="nav-icon">👤</span>
                <span>Profile</span>
            </a>
        </div>
    </div>
    
    <script>
        function addToSearch(char) {
            const input = document.querySelector('input[name="q"]');
            input.value += char;
            input.focus();
        }
        
        function clearSearch() {
            document.querySelector('input[name="q"]').value = '';
            document.querySelector('.clear-btn').style.display = 'none';
            document.getElementById('searchForm').submit();
        }
        
        function quickAdd(productId) {
            fetch('quick_add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Added to cart!');
                    location.reload();
                }
            });
        }
        
        document.querySelector('input[name="q"]')?.addEventListener('input', function() {
            const clearBtn = document.querySelector('.clear-btn');
            if (clearBtn) {
                clearBtn.style.display = this.value.length > 0 ? 'block' : 'none';
            }
        });
    </script>
</body>
</html>