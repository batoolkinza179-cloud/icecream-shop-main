<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'icecream_shop');

define('SITE_NAME', 'Blue Sky Summer');
define('SITE_URL', 'http://localhost/icecream-shop/');
define('ADMIN_URL', SITE_URL . 'admin/');
define('USER_URL', SITE_URL . 'user/');
define('ASSETS_URL', SITE_URL . 'assets/');

define('ROOT_PATH', dirname(__DIR__) . '/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('USER_PATH', ROOT_PATH . 'user/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('UPLOAD_PATH', ROOT_PATH . 'assets/images/uploads/');
define('UPLOAD_URL', ASSETS_URL . 'images/uploads/');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim(htmlspecialchars($input)));
}

function getCartCount() {
    if (isset($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    }
    return 0;
}
?>