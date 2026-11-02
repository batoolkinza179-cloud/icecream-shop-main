<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ic_db";

// Create connection with error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//$conn = new mysqli($servername, $username, $password, $dbname);
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname,
    3307
);
$conn->set_charset("utf8mb4");

// Check if connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isAdminLoggedIn() {
    return isset($_SESSION['admin_username']);
}

function isUserLoggedIn() {
    return isset($_SESSION['username']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim(htmlspecialchars($input)));
}

// Check and add category and type columns to products table if they don't exist
$check_category = $conn->query("SHOW COLUMNS FROM products LIKE 'category'");
if ($check_category->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN category VARCHAR(50) AFTER product_name");
}

$check_type = $conn->query("SHOW COLUMNS FROM products LIKE 'type'");
if ($check_type->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN type VARCHAR(50) AFTER category");
}

// Update existing products with sample categories if they are NULL
$conn->query("UPDATE products SET category = 'Fruit' WHERE (product_name LIKE '%Strawberry%' OR product_name LIKE '%Mango%' OR product_name LIKE '%Cherry%' OR product_name LIKE '%Berry%') AND (category IS NULL OR category = '')");
$conn->query("UPDATE products SET category = 'Classic' WHERE (product_name LIKE '%Vanilla%') AND (category IS NULL OR category = '')");
$conn->query("UPDATE products SET category = 'Chocolate' WHERE (product_name LIKE '%Chocolate%' OR product_name LIKE '%Fudge%' OR product_name LIKE '%Cookies%') AND (category IS NULL OR category = '')");
$conn->query("UPDATE products SET category = 'Nut' WHERE (product_name LIKE '%Pecan%' OR product_name LIKE '%Nut%') AND (category IS NULL OR category = '')");
$conn->query("UPDATE products SET category = 'Special' WHERE category IS NULL OR category = ''");

// Update product types
$conn->query("UPDATE products SET type = 'Ice Cream' WHERE (category != 'Fruit' OR category IS NULL) AND (type IS NULL OR type = '')");
$conn->query("UPDATE products SET type = 'Sorbet' WHERE product_name LIKE '%Sorbet%' AND (type IS NULL OR type = '')");
$conn->query("UPDATE products SET type = 'Gelato' WHERE product_name LIKE '%Gelato%' AND (type IS NULL OR type = '')");
$conn->query("UPDATE products SET type = 'Frozen Yogurt' WHERE product_name LIKE '%Yogurt%' AND (type IS NULL OR type = '')");
?>