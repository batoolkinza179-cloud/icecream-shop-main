<?php
session_start();
require_once '../admin/config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Check if it's an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

$limit = 8;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT * FROM products WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
$params = [];
$count_params = [];
$types = "";
$count_types = "";

if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND (product_name LIKE ? OR description LIKE ? OR ingredients LIKE ? OR category LIKE ? OR type LIKE ?)";
    $count_sql .= " AND (product_name LIKE ? OR description LIKE ? OR ingredients LIKE ? OR category LIKE ? OR type LIKE ?)";
    for($i=0; $i<5; $i++) {
        $params[] = $search_term;
        $count_params[] = $search_term;
    }
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

// Get total count
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get products
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Generate HTML
$html = '';
if ($products->num_rows > 0) {
    while($item = $products->fetch_assoc()) {
        $html .= '<div class="menu-card">';
        if(!empty($item['category'])) {
            $html .= '<span class="menu-badge">' . htmlspecialchars($item['category']) . '</span>';
        }
        $html .= '<img src="' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['product_name']) . '" class="menu-image" loading="lazy">';
        $html .= '<div class="menu-content">';
        $html .= '<h3 class="menu-title">' . htmlspecialchars($item['product_name']) . '</h3>';
        if(!empty($item['type'])) {
            $html .= '<div class="menu-category"><span>🍨</span> ' . htmlspecialchars($item['type']) . '</div>';
        }
        $html .= '<p class="menu-description">' . htmlspecialchars($item['description']) . '</p>';
        if(!empty($item['ingredients'])) {
            $html .= '<div class="menu-ingredients"><strong>Ingredients:</strong> ' . htmlspecialchars($item['ingredients']) . '</div>';
        }
        $html .= '<div class="menu-price">₹' . number_format($item['price'], 2) . '</div>';
        $html .= '<form method="POST" class="add-to-cart-form">';
        $html .= '<input type="hidden" name="item_id" value="' . $item['id'] . '">';
        $html .= '<input type="hidden" name="add_to_cart" value="1">';
        
        // Preserve filters
        if(!empty($search)) $html .= '<input type="hidden" name="search" value="' . htmlspecialchars($search) . '">';
        if(!empty($category)) $html .= '<input type="hidden" name="category" value="' . htmlspecialchars($category) . '">';
        if(!empty($type)) $html .= '<input type="hidden" name="type" value="' . htmlspecialchars($type) . '">';
        if(!empty($price_range)) $html .= '<input type="hidden" name="price_range" value="' . htmlspecialchars($price_range) . '">';
        if($sort != 'id_desc') $html .= '<input type="hidden" name="sort" value="' . htmlspecialchars($sort) . '">';
        if($page > 1) $html .= '<input type="hidden" name="page" value="' . $page . '">';
        
        $html .= '<input type="number" name="quantity" min="1" value="1" class="quantity-input">';
        $html .= '<button type="submit" class="cart-btn">Add to Cart</button>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
    }
} else {
    $html .= '<div class="no-results">';
    $html .= '<h3>😢 No products found</h3>';
    $html .= '<p>Try adjusting your search or filter criteria</p>';
    $html .= '<a href="menu.php" class="btn btn-primary">View All Products</a>';
    $html .= '</div>';
}

// Generate pagination HTML
$pagination = '';
if ($total_pages > 1) {
    $pagination .= '<div class="pagination">';
    
    // Build query string for pagination links
    $query_params = [];
    if(!empty($search)) $query_params['search'] = $search;
    if(!empty($category)) $query_params['category'] = $category;
    if(!empty($type)) $query_params['type'] = $type;
    if(!empty($price_range)) $query_params['price_range'] = $price_range;
    if($sort != 'id_desc') $query_params['sort'] = $sort;
    
    if ($page > 1) {
        $query_params['page'] = $page - 1;
        $pagination .= '<a href="?' . http_build_query($query_params) . '" class="page-link">←</a>';
    }
    
    for ($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++) {
        $query_params['page'] = $i;
        $active = $i == $page ? ' active' : '';
        $pagination .= '<a href="?' . http_build_query($query_params) . '" class="page-link' . $active . '">' . $i . '</a>';
    }
    
    if ($page < $total_pages) {
        $query_params['page'] = $page + 1;
        $pagination .= '<a href="?' . http_build_query($query_params) . '" class="page-link">→</a>';
    }
    
    $pagination .= '</div>';
}

// Return JSON response
echo json_encode([
    'html' => $html,
    'total' => $total_rows,
    'page' => $page,
    'total_pages' => $total_pages,
    'pagination' => $pagination
]);
?>